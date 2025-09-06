<div class="col-md-3 sidebar">
    <ul class="nav nav-pills nav-stacked">
        <li class="has-submenu {{ Route::is('menu.submenu1', 'menu.submenu2', 'menu.submenu3') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-toggle {{ Route::is('menu.submenu1', 'menu.submenu2', 'menu.submenu3') ? 'active' : '' }}">
                Main Menu
            </a>
            <div class="submenu" style="{{ Route::is('menu.submenu1', 'menu.submenu2', 'menu.submenu3') ? 'display:block;' : '' }}">
                <a href="{{ route('menu.submenu1') }}" class="{{ Route::is('menu.submenu1') ? 'active' : '' }}">Submenu 1</a>
                <a href="{{ route('menu.submenu2') }}" class="{{ Route::is('menu.submenu2') ? 'active' : '' }}">Submenu 2</a>
                <a href="{{ route('menu.submenu3') }}" class="{{ Route::is('menu.submenu3') ? 'active' : '' }}">Submenu 3</a>
            </div>
        </li>

        {{-- <li class="has-submenu {{ Route::is('menu.submenu4', 'menu.submenu5') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-toggle {{ Route::is('menu.submenu4', 'menu.submenu5') ? 'active' : '' }}">
                Main Menu 1
            </a>
            <div class="submenu" style="{{ Route::is('menu.submenu4', 'menu.submenu5') ? 'display:block;' : '' }}">
                <a href="{{ route('menu.submenu4') }}" class="{{ Route::is('menu.submenu4') ? 'active' : '' }}">Submenu 4</a>
                <a href="{{ route('menu.submenu5') }}" class="{{ Route::is('menu.submenu5') ? 'active' : '' }}">Submenu 5</a>
            </div>
        </li> --}}
    </ul>
</div>
