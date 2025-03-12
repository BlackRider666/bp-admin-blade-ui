<app-header
        username="{{ Auth::check()?Auth::user()->name:'' }}"
        email="{{ Auth::check()?Auth::user()->email:'' }}"
        logoutRoute="{{ route('bpadmin.auth.logout') }}"
        csrfToken="{{ csrf_token() }}"
        title="{{config('bpadmin.title')}}"
        logo="{{config('bpadmin.logo_url')}}"
></app-header>
