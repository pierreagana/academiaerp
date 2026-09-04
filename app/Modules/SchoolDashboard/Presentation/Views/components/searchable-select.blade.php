{{-- Global searchable-dropdown enhancement: every plain <select> on the page becomes
     a searchable combobox (Tom Select), styled to match the app's Tailwind look.
     Include this once near the end of <head> or <body> in any root layout.
     Opt a select out with the `data-no-search` attribute. --}}
<style>
    .ts-wrapper { font-family: inherit; width: 100%; }
    .ts-wrapper.single .ts-control, .ts-wrapper.multi .ts-control {
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 13px;
        color: #0F172A;
        min-height: unset;
        box-shadow: none;
    }
    .ts-wrapper.single .ts-control { padding-right: 1.75rem; }
    .ts-wrapper.focus .ts-control { border-color: #031C5B; }
    .ts-wrapper .ts-control input { font-size: 13px; color: #0F172A; }
    .ts-wrapper .ts-control input::placeholder { color: #94A3B8; }
    .ts-dropdown {
        border: 1px solid #E2E8F0;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
        font-size: 13px;
        margin-top: 4px;
        z-index: 9999;
    }
    .ts-dropdown .ts-dropdown-content { padding: 4px; }
    .ts-dropdown .option, .ts-dropdown .optgroup-header {
        padding: 0.5rem 0.625rem;
        border-radius: 0.375rem;
    }
    .ts-dropdown .optgroup-header { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: #64748B; }
    .ts-dropdown .option.active { background-color: #EFF3FF; color: #031C5B; }
    .ts-dropdown .option.selected { background-color: #031C5B; color: #fff; }
    .ts-wrapper.disabled .ts-control { background-color: #F1F5F9; opacity: .6; }
    .ts-wrapper .item { background: transparent; }
    .ts-wrapper.single .ts-control > .item { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    /* While actively searching, the stale "current value" chip must not sit next to the typed text. */
    .ts-wrapper.single.input-active .ts-control > .item { display: none; }
</style>
<script>
    (function () {
        function enhance(select) {
            if (!select || select.tomselect || select.multiple || select.hasAttribute('data-no-search')) return;
            var originalClasses = select.className;
            var ts = new TomSelect(select, {
                create: false,
                allowEmptyOption: true,
                maxOptions: null,
                // Renders the flyout as a direct child of <body> instead of next to the
                // control — otherwise it silently gets clipped by any ancestor card/table
                // wrapper using overflow-hidden or its own stacking context, even though
                // the dropdown's own position/size math is completely correct.
                dropdownParent: 'body',
                onChange: function () {
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            // Tom Select copies the original <select>'s own classes onto its new wrapper —
            // harmless for plain elements, but Tailwind utility classes meant for the
            // original control (w-full, appearance-none, px-4 py-2, ...) end up sized/padded
            // wrong on the wrapper and throw off Tom Select's own dropdown-position math
            // (it can render the flyout far off to the side). Strip them back off; our own
            // CSS above already styles .ts-control directly.
            if (originalClasses) {
                originalClasses.split(/\s+/).filter(Boolean).forEach(function (cls) {
                    ts.wrapper.classList.remove(cls);
                });
            }
            // Tom Select's wrapper is a sibling element it creates once at init — it has no
            // idea when Alpine's x-show later toggles the (now-hidden) original select's
            // inline display, so we mirror that onto the wrapper ourselves.
            function syncVisibility() {
                ts.wrapper.style.display = select.style.display === 'none' ? 'none' : '';
            }
            syncVisibility();

            // Guarded against a re-entrant loop: Tom Select's own disable()/enable() rewrite
            // the `disabled` attribute unconditionally, which would otherwise re-trigger this
            // same observer forever every time Alpine's :disabled binding toggles it.
            var lastDisabled = select.disabled;
            var attrObserver = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    if (m.attributeName === 'disabled' && select.disabled !== lastDisabled) {
                        lastDisabled = select.disabled;
                        select.disabled ? ts.disable() : ts.enable();
                    }
                    if (m.attributeName === 'style') {
                        syncVisibility();
                    }
                });
            });
            attrObserver.observe(select, { attributes: true, attributeFilter: ['disabled', 'style'] });
        }

        function enhanceAll(root) {
            root.querySelectorAll('select').forEach(enhance);
        }

        document.addEventListener('DOMContentLoaded', function () {
            enhanceAll(document);

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (node.tagName === 'SELECT') enhance(node);
                        else if (node.querySelectorAll) enhanceAll(node);
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    })();
</script>
