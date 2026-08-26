import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Business dashboard navigation: keep the existing routes, but make the
// information architecture clearer and provide the same navigation on mobile.
document.addEventListener('DOMContentLoaded', () => {
    if (!window.location.pathname.match(/^\/business\/dashboard\/?$/)) return;

    const sidebar = document.querySelector('aside');
    const nav = sidebar?.querySelector('nav');
    if (!sidebar || !nav) return;

    const links = [...nav.querySelectorAll('a')];
    const labels = links.map(link => link.textContent.trim());
    const find = (label) => links.find(link => link.textContent.trim() === label);

    const section = (title) => {
        const el = document.createElement('p');
        el.className = 'px-4 pb-2 pt-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 first:pt-0';
        el.textContent = title;
        return el;
    };

    const groups = [
        ['OVERVIEW', ['Dashboard']],
        ['BUILD YOUR BUSINESS', ['Programs', 'Products', 'Affiliates', 'Recruitment Network']],
        ['MARKETING & AUDIENCE', ['Marketing Campaigns', 'Create Campaign', 'Social Follow']],
        ['MONEY', ['Sales', 'Commissions', 'Payouts']],
    ];

    nav.innerHTML = '';
    nav.className = 'flex-1 overflow-y-auto p-4';

    groups.forEach(([title, groupLabels]) => {
        nav.appendChild(section(title));
        groupLabels.forEach(label => {
            const link = find(label);
            if (!link) return;
            link.className = 'group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition ' +
                (window.location.pathname === new URL(link.href).pathname
                    ? 'bg-violet-50 text-violet-700 shadow-sm'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-violet-700');
            const dot = document.createElement('span');
            dot.className = 'grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-[10px] font-black text-slate-500 group-hover:bg-violet-100 group-hover:text-violet-700';
            dot.textContent = label === 'Dashboard' ? '⌂' : label === 'Payouts' ? '₦' : label === 'Social Follow' ? 'S' : '•';
            link.prepend(dot);
            nav.appendChild(link);
        });
    });

    // Make the current dashboard identity and settings area clearer.
    const brand = sidebar.querySelector('a[href*="business/dashboard"]');
    if (brand) brand.className = 'text-xl font-black tracking-tight text-slate-950';

    const settings = sidebar.querySelector('a[href*="profile"]');
    if (settings) {
        settings.className = 'block rounded-xl px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-violet-700';
        settings.textContent = '⚙  Settings';
    }

    // Mobile navigation drawer.
    const mobileButton = document.createElement('button');
    mobileButton.type = 'button';
    mobileButton.setAttribute('aria-label', 'Open business navigation');
    mobileButton.setAttribute('aria-expanded', 'false');
    mobileButton.className = 'inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden';
    mobileButton.innerHTML = '<span class="text-xl leading-none">☰</span>';

    const header = document.querySelector('main header');
    const headerFirst = header?.querySelector('div');
    if (headerFirst) {
        headerFirst.classList.add('relative');
        headerFirst.prepend(mobileButton);
        mobileButton.classList.add('absolute', 'right-0', 'top-0');
    }

    const drawer = document.createElement('div');
    drawer.className = 'fixed inset-0 z-[100] hidden lg:hidden';
    drawer.innerHTML = `
        <div data-business-nav-overlay class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"></div>
        <aside class="relative flex h-full w-[86vw] max-w-sm flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b p-6">
                <div>
                    <p class="text-xl font-black tracking-tight text-slate-950">AIPM</p>
                    <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-violet-600">Business Hub</p>
                </div>
                <button type="button" data-business-nav-close class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl text-slate-600">×</button>
            </div>
            <div data-business-mobile-nav class="flex-1 overflow-y-auto p-4"></div>
            <div class="border-t p-4">
                <a href="${settings?.href ?? '#'}" class="block rounded-xl px-4 py-3 text-sm font-bold text-slate-600">⚙ Settings</a>
            </div>
        </aside>`;
    document.body.appendChild(drawer);

    const mobileNav = drawer.querySelector('[data-business-mobile-nav]');
    groups.forEach(([title, groupLabels]) => {
        const heading = section(title);
        mobileNav.appendChild(heading);
        groupLabels.forEach(label => {
            const source = links.find(link => link.textContent.trim().replace(/^.*?([A-Za-z].*)$/, '$1') === label) || links.find(link => link.textContent.includes(label));
            if (!source) return;
            const item = source.cloneNode(true);
            item.className = 'flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-bold text-slate-700 transition hover:bg-violet-50 hover:text-violet-700';
            mobileNav.appendChild(item);
        });
    });

    const closeDrawer = () => {
        drawer.classList.add('hidden');
        mobileButton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
    };
    const openDrawer = () => {
        drawer.classList.remove('hidden');
        mobileButton.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
    };

    mobileButton.addEventListener('click', openDrawer);
    drawer.querySelector('[data-business-nav-overlay]').addEventListener('click', closeDrawer);
    drawer.querySelector('[data-business-nav-close]').addEventListener('click', closeDrawer);
    drawer.querySelectorAll('a').forEach(link => link.addEventListener('click', closeDrawer));
});
