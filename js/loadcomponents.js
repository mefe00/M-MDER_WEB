// load-components.js

function loadComponent(id, file, callback) {
    fetch(file)
        .then(response => {
            if (!response.ok) throw new Error(`Dosya bulunamadı: ${file}`);
            return response.text();
        })
        .then(data => {
            document.getElementById(id).innerHTML = data;
            if (typeof callback === "function") callback();
        })
        .catch(error => {
            console.error('Hata:', error);
        });
}

// Navbar ve footer'ı yükle
document.addEventListener("DOMContentLoaded", () => {
    loadComponent("navbar-placeholder", "navbar.html", function() {
        // Hamburger ve offcanvas menü
        const mobileMenu = document.getElementById("mobile-menu");
        const offcanvasMenu = document.getElementById("offcanvas-menu");
        const offcanvasBackdrop = document.getElementById("offcanvas-backdrop");
        const offcanvasClose = document.getElementById("offcanvas-close");

        function openMenu() {
            offcanvasMenu.classList.add("active");
            offcanvasBackdrop.classList.add("active");
            document.body.style.overflow = "hidden";
        }
        function closeMenu() {
            offcanvasMenu.classList.remove("active");
            offcanvasBackdrop.classList.remove("active");
            document.body.style.overflow = "";
        }

        if (mobileMenu && offcanvasMenu && offcanvasBackdrop && offcanvasClose) {
            mobileMenu.addEventListener("click", openMenu);
            offcanvasClose.addEventListener("click", closeMenu);
            offcanvasBackdrop.addEventListener("click", closeMenu);
        }
    });
    loadComponent("footer-placeholder", "footer.html");
});

