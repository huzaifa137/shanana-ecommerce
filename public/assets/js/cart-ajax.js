/**
 * Shanana — global cart + navigation UX helpers.
 *
 * 1. Add-to-cart forms (route: shop/add-to-cart/{id}) are intercepted and
 *    submitted over AJAX. While the request is in flight the whole form is
 *    disabled and the button shows a real pending state (spinner + "Adding…"
 *    label) so the user has clear feedback that something happened. Once it
 *    resolves, the button/form is flipped in place to the "In Cart" state
 *    and a small SweetAlert2 toast confirms it in the corner of the screen —
 *    no page reload. Every header cart badge (".js-cart-count"/".js-cart-badge")
 *    on the current page is updated and revealed at the same time, so the
 *    count is correct instantly, on every page, without a reload.
 *
 * 2. Any link that navigates to another page shows the site's existing
 *    #spinner overlay immediately on click, instead of the page looking
 *    unresponsive until the new page has loaded.
 *
 * This file is loaded once, globally, from layouts/footer.blade.php, so it
 * covers every "Add to cart" button across the home page, shop, category
 * pages, product detail page and the customer dashboard without needing
 * page-specific script blocks.
 */
(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var cartToast =
            typeof Swal !== "undefined" &&
            Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                didOpen: function (toast) {
                    toast.addEventListener("mouseenter", Swal.stopTimer);
                    toast.addEventListener("mouseleave", Swal.resumeTimer);
                },
            });

        function notify(icon, title) {
            if (cartToast) {
                cartToast.fire({ icon: icon, title: title });
            }
        }

        function updateCartCount(count) {
            document.querySelectorAll(".js-cart-count").forEach(function (el) {
                el.textContent = count;
            });
            // Badges are always present in the DOM (hidden via .d-none when the
            // cart is empty) so they can be revealed here instantly — no reload
            // needed to see the count appear for the first time.
            document.querySelectorAll(".js-cart-badge").forEach(function (el) {
                el.classList.toggle("d-none", !(count > 0));
            });
        }

        // Flip a successfully-submitted add-to-cart form into its
        // "In Cart" look, whichever of the card layouts it belongs to.
        function markAsAdded(form) {
            var button = form.querySelector('button[type="submit"], a.modern-add-btn');

            form.querySelectorAll("input, button").forEach(function (el) {
                el.disabled = true;
            });

            if (!button) {
                form.classList.add("added-to-cart");
                return;
            }

            if (button.classList.contains("modern-add-btn")) {
                button.innerHTML = '<svg width="14" height="14"><use xlink:href="#check"></use></svg> In Cart';
                button.classList.remove("modern-add-btn");
                button.classList.add("modern-added-btn");
            } else {
                button.innerHTML = '<i class="fa fa-check me-2 text-success"></i> In Cart';
                button.classList.remove("border-primary", "border-secondary", "text-primary");
                button.classList.add("border-success", "text-success");
            }

            form.classList.add("added-to-cart");
        }

        function isAddToCartForm(form) {
            return /\/shop\/add-to-cart\//.test(form.getAttribute("action") || "");
        }

        // --- Add to cart (AJAX) ---------------------------------------
        document.addEventListener("submit", function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement) || !isAddToCartForm(form)) return;

            e.preventDefault();

            if (form.dataset.submitting === "1") return;
            form.dataset.submitting = "1";

            var button = form.querySelector('button[type="submit"], a.modern-add-btn');
            var originalHtml = button ? button.innerHTML : "";

            // Snapshot FormData BEFORE disabling any fields.
            // Disabled inputs are excluded from FormData serialisation, so if
            // we disable first (to block double-clicks) the CSRF _token field
            // disappears from the payload → Laravel returns 419 → "Request failed".
            var formData = new FormData(form);

            // Now it is safe to lock the form — the payload is already captured.
            form.querySelectorAll("input, button").forEach(function (el) {
                el.disabled = true;
            });

            if (button) {
                button.classList.add("is-adding-to-cart");
                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:6px;vertical-align:-2px;"></span>' +
                    '<span class="js-adding-label">Adding…</span>';
            }

            fetch(form.getAttribute("action"), {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            })
                .then(function (response) {
                    if (!response.ok) throw new Error("Request failed");
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !data.success) {
                        throw new Error((data && data.message) || "Could not add product to cart.");
                    }

                    markAsAdded(form);

                    if (typeof data.cart_count !== "undefined") {
                        updateCartCount(data.cart_count);
                    }

                    notify("success", data.message || "Added to cart!");
                })
                .catch(function (err) {
                    form.querySelectorAll("input, button").forEach(function (el) {
                        el.disabled = false;
                    });
                    if (button) {
                        button.classList.remove("is-adding-to-cart");
                        button.innerHTML = originalHtml;
                    }
                    form.dataset.submitting = "0";
                    notify("error", err.message || "Could not add product to cart.");
                });
        });

        // --- Immediate loader on navigation -----------------------------
        // Two different loading-overlay markups exist in this codebase:
        // the shared layout uses #spinner (layouts/header.blade.php),
        // while the standalone home page uses its own .preloader-wrapper
        // (Ecommerce/home.blade.php). Support both so this works everywhere.
        var spinner = document.getElementById("spinner");
        var preloader = document.querySelector(".preloader-wrapper");
        if (!spinner && !preloader) return;

        function showLoader() {
            if (spinner) spinner.classList.add("show");
            if (preloader) {
                preloader.style.display = "block";
                preloader.style.opacity = "1";
            }
        }

        function hideLoader() {
            if (spinner) spinner.classList.remove("show");
            if (preloader) preloader.style.display = "none";
        }

        document.addEventListener("click", function (e) {
            var link = e.target.closest("a[href]");
            if (!link) return;

            var href = link.getAttribute("href");
            if (
                !href ||
                href.charAt(0) === "#" ||
                href.indexOf("javascript:") === 0 ||
                href.indexOf("mailto:") === 0 ||
                href.indexOf("tel:") === 0
            ) {
                return;
            }
            if (link.target === "_blank" || link.hasAttribute("download")) return;
            if (link.dataset.noLoader !== undefined) return;
            // These links run their own confirm/spinner flow (SweetAlert) first.
            if (link.classList.contains("remove-from-cart")) return;
            // Bootstrap offcanvas/modal toggles (e.g. the cart flyout) don't navigate.
            if (link.hasAttribute("data-bs-toggle")) return;

            showLoader();
        });

        // Coming back via the browser's back/forward cache should not
        // leave the overlay stuck on screen.
        window.addEventListener("pageshow", function (e) {
            if (e.persisted) hideLoader();
        });
    });
})();
