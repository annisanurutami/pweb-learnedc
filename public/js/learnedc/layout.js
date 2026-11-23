/* Perilaku layout Learn's EDC: sidebar mobile, menu profil, dialog keluar, jam WIB, pencarian tabel. */
(function () {
    'use strict';

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    /* Sidebar mobile */
    const sidebar = $('.sidebar-admin');
    const backdrop = $('.sidebar-backdrop');

    function aturSidebar(buka) {
        if (sidebar) sidebar.classList.toggle('open', buka);
        if (backdrop) backdrop.classList.toggle('show', buka);
    }

    const tombolBuka = $('.navbar-toggle-btn');
    const tombolTutup = $('.sidebar-close-btn');
    if (tombolBuka) tombolBuka.addEventListener('click', () => aturSidebar(true));
    if (tombolTutup) tombolTutup.addEventListener('click', () => aturSidebar(false));
    if (backdrop) backdrop.addEventListener('click', () => aturSidebar(false));

    /* Menu profil */
    const menuProfil = $('#menu-profil');
    const tombolProfil = $('[data-profil-toggle]');

    function aturMenuProfil(buka) {
        if (!menuProfil || !tombolProfil) return;
        menuProfil.classList.toggle('show', buka);
        tombolProfil.setAttribute('aria-expanded', buka ? 'true' : 'false');
    }

    if (tombolProfil) {
        tombolProfil.addEventListener('click', (e) => {
            e.stopPropagation();
            aturMenuProfil(!menuProfil.classList.contains('show'));
        });
    }

    document.addEventListener('click', (e) => {
        if (menuProfil && !menuProfil.contains(e.target)) aturMenuProfil(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            aturSidebar(false);
            aturMenuProfil(false);
        }
    });

    /* Dialog keluar */
    const dialogKeluar = $('#dialog-keluar');

    $$('[data-keluar]').forEach((el) => {
        el.addEventListener('click', () => {
            aturMenuProfil(false);
            if (dialogKeluar && typeof dialogKeluar.showModal === 'function') {
                dialogKeluar.showModal();
            } else if (dialogKeluar) {
                // Peramban tanpa <dialog>: kirim form keluar langsung.
                const form = $('form', dialogKeluar);
                if (form) form.submit();
            }
        });
    });

    $$('[data-tutup-dialog]').forEach((el) => {
        el.addEventListener('click', () => dialogKeluar && dialogKeluar.close());
    });

    /* Jam WIB */
    const elJam = $('.time-hour');
    const elTanggal = $('.time-date');

    if (elJam && elTanggal && window.Intl) {
        const formatJam = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
        });
        const formatTanggal = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        });

        const perbarui = () => {
            const sekarang = new Date();
            elJam.textContent = formatJam.format(sekarang).replace(/\./g, ':');
            elTanggal.textContent = formatTanggal.format(sekarang);
        };

        perbarui();
        setInterval(perbarui, 1000);
    }

    /* Pencarian cepat: menyaring baris tabel pada halaman yang sedang dibuka */
    const kolomCari = $('.search-input');

    if (kolomCari) {
        kolomCari.addEventListener('input', () => {
            const kata = kolomCari.value.trim().toLowerCase();

            $$('.app-slot tbody tr').forEach((baris) => {
                // Baris pesan kosong ("Belum ada data") tidak ikut disaring.
                if ($('td[colspan]', baris)) return;
                baris.hidden = kata !== '' && !baris.textContent.toLowerCase().includes(kata);
            });
        });
    }
})();
