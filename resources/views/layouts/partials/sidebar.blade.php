<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>

    <div>
        <a href="#" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>

    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">

            {{-- Dashboard (semua role) --}}
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active-page' : '' }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>

            @if(auth()->user()->role === 'adminsuper')

                <li class="sidebar-menu-group-title">Master Data</li>

                <li>
                    <a href="{{ route('business-partners.index') }}" class="{{ request()->is('business-partners*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:building-2" class="menu-icon"></iconify-icon>
                        <span>Akun BP</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('customers.index') }}" class="{{ request()->is('customers*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:users" class="menu-icon"></iconify-icon>
                        <span>Akun Customer</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('technicians.index') }}" class="{{ request()->is('technicians*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:user-cog" class="menu-icon"></iconify-icon>
                        <span>Akun Teknisi</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('service-types.index') }}" class="{{ request()->is('service-types*') ? 'active-page' : '' }}">
                        <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                        <span>Jenis Layanan</span>
                    </a>
                </li>

                <li class="sidebar-menu-group-title">Operasional</li>

                <li>
                    <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active-page' : '' }}">
                        <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                        <span>Orders</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('complaints.index') }}" class="{{ request()->is('complaints*') ? 'active-page' : '' }}">
                        <iconify-icon icon="mage:message-question-mark-round" class="menu-icon"></iconify-icon>
                        <span>Komplain & Garansi</span>
                    </a>
                </li>

                <li class="sidebar-menu-group-title">Keuangan</li>

                <li>
                    <a href="{{ route('payments.index') }}" class="{{ request()->is('payments*') ? 'active-page' : '' }}">
                        <iconify-icon icon="hugeicons:money-send-square" class="menu-icon"></iconify-icon>
                        <span>Pembayaran</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('wallets.index') }}" class="{{ request()->is('wallets*') ? 'active-page' : '' }}">
                        <iconify-icon icon="hugeicons:bitcoin-circle" class="menu-icon"></iconify-icon>
                        <span>Wallet</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('withdrawals.index') }}" class="{{ request()->is('withdrawals*') ? 'active-page' : '' }}">
                        <iconify-icon icon="solar:card-transfer-outline" class="menu-icon"></iconify-icon>
                        <span>Penarikan Saldo</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('coupons.index') }}" class="{{ request()->is('coupons*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:ticket" class="menu-icon"></iconify-icon>
                        <span>Kupon</span>
                    </a>
                </li>
             <li class="sidebar-menu-group-title">Settings</li>

                <li>
                    <a href="{{ route('settings.index') }}" class="{{ request()->is('settings*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:settings" class="menu-icon"></iconify-icon>
                        <span>Kontak</span>
                    </a>
                </li>   
                <li>
                    <a href="{{ route('articles.index') }}" class="{{ request()->is('articles*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:newspaper" class="menu-icon"></iconify-icon>
                        <span>Promo & Tips</span>
                    </a>
                </li> 

            @elseif(auth()->user()->role === 'business_partner')

                <li class="sidebar-menu-group-title">Master Data</li>

                <li>
                    <a href="{{ route('bp-services.index') }}" class="{{ request()->is('bp-services*') ? 'active-page' : '' }}">
                        <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                        <span>Layanan Saya</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('bp-technicians.index') }}" class="{{ request()->is('bp-technicians*') ? 'active-page' : '' }}">
                        <iconify-icon icon="solar:user-id-outline" class="menu-icon"></iconify-icon>
                        <span>Teknisi Lokal</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('bp-technicians.approval') }}" class="{{ request()->is('bp-approvals*') ? 'active-page' : '' }}">
                        <iconify-icon icon="lucide:user-check" class="menu-icon"></iconify-icon>
                        <span>Approval Teknisi</span>
                    </a>
                </li>

                <li class="sidebar-menu-group-title">Operasional</li>

                <li>
                    <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active-page' : '' }}">
                        <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                        <span>Order Area Saya</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('complaints.index') }}" class="{{ request()->is('complaints*') ? 'active-page' : '' }}">
                        <iconify-icon icon="mage:message-question-mark-round" class="menu-icon"></iconify-icon>
                        <span>Komplain & Garansi</span>
                    </a>
                </li>

            @endif

            {{-- Pengaturan (semua role) --}}
            <li class="sidebar-menu-group-title">Akun</li>

            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="text-danger-600">
                    <iconify-icon icon="lucide:log-out" class="menu-icon"></iconify-icon>
                    <span>Logout</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>