import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

function register(A) {
  A.plugin(collapse)

  A.store('ui', {
    sidebarCollapsed: false,
    paletteOpen: false,
    toasts: [],
    theme: 'dark',

    init() {
      // sidebar
      try {
        this.sidebarCollapsed = localStorage.getItem('bp:sidebar:collapsed') === '1'
      } catch (e) { /* localStorage unavailable */ }

      // theme — read explicit choice, fall back to system preference
      let stored = null
      try { stored = localStorage.getItem('bp:theme') } catch (e) {}
      if (stored === 'light' || stored === 'dark') {
        this.theme = stored
      } else {
        this.theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
      }
      this.applyTheme(this.theme)

      // follow OS changes only while no explicit choice exists
      if (window.matchMedia) {
        const mq = window.matchMedia('(prefers-color-scheme: dark)')
        const handler = (e) => {
          let s = null
          try { s = localStorage.getItem('bp:theme') } catch (err) {}
          if (s === null) {
            this.theme = e.matches ? 'dark' : 'light'
            this.applyTheme(this.theme)
          }
        }
        if (mq.addEventListener) mq.addEventListener('change', handler)
        else if (mq.addListener) mq.addListener(handler)
      }
    },

    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
      try { localStorage.setItem('bp:sidebar:collapsed', this.sidebarCollapsed ? '1' : '0') } catch (e) {}
    },

    applyTheme(t) {
      document.documentElement.setAttribute('data-theme', t)
    },

    toggleTheme() {
      this.theme = this.theme === 'dark' ? 'light' : 'dark'
      try { localStorage.setItem('bp:theme', this.theme) } catch (e) {}
      this.applyTheme(this.theme)
    },

    openPalette() { this.paletteOpen = true },
    closePalette() { this.paletteOpen = false },

    toast(msg, kind = 'info', ttl = 4000) {
      const id = Date.now() + Math.random()
      this.toasts.push({ id, msg, kind })
      setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id) }, ttl)
    },
    dismissToast(id) { this.toasts = this.toasts.filter(t => t.id !== id) },
  })

  A.store('confirm', {
    open: false,
    title: 'Are you sure?',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    danger: false,
    _onConfirm: null,
    ask(opts = {}) {
      this.title       = opts.title       || 'Are you sure?'
      this.message     = opts.message     || ''
      this.confirmText = opts.confirmText || 'Confirm'
      this.cancelText  = opts.cancelText  || 'Cancel'
      this.danger      = !!opts.danger
      this._onConfirm  = typeof opts.onConfirm === 'function' ? opts.onConfirm : null
      this.open        = true
    },
    close() { this.open = false; this._onConfirm = null },
    confirm() {
      const cb = this._onConfirm
      this.open = false
      this._onConfirm = null
      if (cb) cb()
    },
  })

  A.data('bpBulkSelect', () => ({
    selected: [],
    get allCheckboxes() {
      return Array.from(this.$root.querySelectorAll('tbody input[type=checkbox][value]'))
    },
    get allSelected() {
      const all = this.allCheckboxes
      return all.length > 0 && this.selected.length === all.length
    },
    toggle(id, checked) {
      if (checked && !this.selected.includes(id)) this.selected.push(id)
      else if (!checked) this.selected = this.selected.filter(x => x !== id)
    },
    toggleAll(checked) {
      this.selected = checked ? this.allCheckboxes.map(el => el.value) : []
    },
    clear() { this.selected = [] },
  }))

  A.data('bpForm', () => ({
    submitting: false,
    dirty: false,
    originalValues: '',
    init() {
      this.originalValues = this.serialize()
      this.$el.addEventListener('input', () => { this.dirty = this.serialize() !== this.originalValues })
      this.$el.addEventListener('change', () => { this.dirty = this.serialize() !== this.originalValues })
      this.$el.addEventListener('submit', () => { this.submitting = true; this.dirty = false })
      const first = this.$el.querySelector('input:not([type=hidden]):not([type=file]),textarea,select')
      if (first && !first.disabled) { setTimeout(() => first.focus(), 50) }
      window.addEventListener('beforeunload', (e) => {
        if (this.dirty && !this.submitting) {
          e.preventDefault()
          e.returnValue = ''
        }
      })
    },
    serialize() {
      const fd = new FormData(this.$el)
      const parts = []
      for (const [k, v] of fd.entries()) {
        if (v instanceof File) continue
        parts.push(k + '=' + v)
      }
      return parts.join('&')
    },
  }))

  // Repeater for hasMany-embed relations on create/edit forms. Clones the
  // blank-row <template> (x-ref="row"), substituting the `__ROW__` index
  // placeholder with a monotonically increasing index so each row submits as a
  // distinct list element (histories[0][...], histories[1][...]). Alpine's
  // mutation observer initialises any x-data inside the cloned row.
  A.data('bpRepeater', (startIndex = 0) => ({
    index: startIndex,
    add() {
      const tpl = this.$refs.row
      const rows = this.$refs.rows
      if (!tpl || !rows) return
      const html = tpl.innerHTML.split('__ROW__').join(String(this.index))
      const wrap = document.createElement('div')
      wrap.innerHTML = html.trim()
      const node = wrap.firstElementChild
      if (!node) return
      rows.appendChild(node)
      this.index++
      const focusable = node.querySelector('input:not([type=hidden]),textarea,select')
      if (focusable) focusable.focus()
    },
  }))

  // Quill rich-text editor wrapper. Uses Alpine x-init so it works in repeater
  // clones (no inline <script> that fails on innerHTML re-injection).
  // id: the container element's id; the hidden input is assumed to be id+'-input'.
  //
  // Bug #3 / A18+A19 fix: defer booting Quill until the container is laid out
  // (offsetParent !== null). Quill 2 reads .offset on a null selection range when
  // initialised inside a display:none element (inactive locale tabs, collapsed
  // repeater rows) → TypeError: Cannot read properties of null (reading 'offset').
  // We check visibility first and fall back to IntersectionObserver so the editor
  // inits correctly when the tab/row becomes visible later.
  A.data('bpQuill', (initialValue) => ({
    content: initialValue || '',
    quill: null,
    _id: null,
    init(id) {
      this._id = id
      if (this._visible()) {
        this._boot()
      } else {
        this._observeVisibility()
      }
    },
    _visible() {
      const el = document.getElementById(this._id)
      return !!(el && el.offsetParent !== null)
    },
    _observeVisibility() {
      const el = document.getElementById(this._id)
      if (!el || typeof IntersectionObserver === 'undefined') { this._boot(); return }
      const io = new IntersectionObserver((entries) => {
        if (entries.some(e => e.isIntersecting) && this._visible()) {
          io.disconnect()
          this._boot()
        }
      })
      io.observe(el)
    },
    _boot() {
      if (this.quill) return
      const container = document.getElementById(this._id)
      const input     = document.getElementById(this._id + '-input')
      if (!container || typeof Quill === 'undefined') return

      this.quill = new Quill(container, {
        theme: 'snow',
        modules: {
          toolbar: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['clean'],
          ],
        },
        placeholder: container.dataset.placeholder || '',
      })

      if (this.content) {
        // Bug #3 / A18: dangerouslyPasteHTML() updates the selection internally,
        // and Quill 2 reads `.offset` on a null selection range (editor inited in
        // a display:none locale tab / collapsed repeater row) → TypeError. Convert
        // the HTML to a Delta and apply it with the 'silent' source, which writes
        // content WITHOUT touching the selection, so no offset read ever happens.
        const delta = this.quill.clipboard.convert({ html: this.content })
        this.quill.setContents(delta, 'silent')
      }

      this.quill.on('text-change', () => {
        const html = this.quill.getText().trim() ? this.quill.root.innerHTML : ''
        this.content = html
        if (input) input.value = html
      })
    },
  }))

  A.data('bpPalette', () => ({
    query: '',
    index: 0,
    entities: [],
    init() {
      try { this.entities = JSON.parse(this.$el.dataset.entities || '[]') } catch (e) { this.entities = [] }
      this.$watch('$store.ui.paletteOpen', open => {
        if (open) {
          this.query = ''; this.index = 0
          this.$nextTick(() => this.$refs.input && this.$refs.input.focus())
        }
      })
    },
    get results() {
      const q = this.query.trim().toLowerCase()
      const list = q === ''
        ? this.entities
        : this.entities.filter(e => e.label.toLowerCase().includes(q) || e.name.toLowerCase().includes(q))
      return list.slice(0, 10)
    },
    move(delta) {
      const n = this.results.length
      if (n === 0) return
      this.index = (this.index + delta + n) % n
    },
    activate() {
      const hit = this.results[this.index]
      if (hit) window.location.href = hit.url
    },
  }))

  A.data('bpCopy', (text) => ({
    copied: false,
    copy() {
      const val = typeof text === 'function' ? text() : text
      const fallback = () => {
        const ta = document.createElement('textarea')
        ta.value = val; ta.style.position = 'fixed'; ta.style.opacity = '0'
        document.body.appendChild(ta); ta.select()
        try { document.execCommand('copy') } catch (e) {}
        document.body.removeChild(ta)
      }
      const done = () => {
        this.copied = true
        if (A.store('ui').toast) A.store('ui').toast('Copied to clipboard', 'success', 1800)
        setTimeout(() => { this.copied = false }, 1500)
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(val).then(done).catch(() => { fallback(); done() })
      } else { fallback(); done() }
    },
  }))

  A.data('bpRelativeTime', (iso) => ({
    label: '',
    timer: null,
    init() { this.refresh(); this.timer = setInterval(() => this.refresh(), 60000) },
    destroy() { if (this.timer) clearInterval(this.timer) },
    refresh() {
      const then = new Date(iso)
      if (isNaN(then.getTime())) { this.label = iso; return }
      const diff = Math.round((Date.now() - then.getTime()) / 1000)
      const abs = Math.abs(diff)
      const units = [[60, 'sec'], [60, 'min'], [24, 'hr'], [7, 'day'], [4.345, 'wk'], [12, 'mo'], [Infinity, 'yr']]
      let val = abs, unit = 'sec'
      for (const [size, name] of units) {
        if (val < size) { unit = name; break }
        val = val / size; unit = name
      }
      val = Math.floor(val)
      this.label = diff < 0 ? `in ${val} ${unit}` : (val <= 1 && unit === 'sec' ? 'just now' : `${val} ${unit} ago`)
    },
  }))

  // Global keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    const target = e.target
    const isInput = target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable || target.tagName === 'SELECT')

    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault()
      A.store('ui').openPalette()
      return
    }
    if (e.key === 'Escape') {
      if (A.store('ui').paletteOpen) A.store('ui').closePalette()
      return
    }
    if (isInput) return
    if (e.key === '[') { e.preventDefault(); A.store('ui').toggleSidebar() }
    if (e.key === 'n') {
      const link = document.querySelector('[data-bp-new]')
      if (link) { e.preventDefault(); window.location.href = link.href }
    }
  })
}

if (!window.Alpine) {
  register(Alpine)
  window.Alpine = Alpine
  Alpine.start()
} else {
  register(window.Alpine)
}
