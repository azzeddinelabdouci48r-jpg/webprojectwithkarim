// ============================================
// عناوين الصفحات لكل رابط في الـ sidebar
// ============================================
const pageTitles = {
    "dashboard.php":       "Vue d'ensemble",
    "gestion_product.php": "Gestion des Produits",
    "gestion_client.php":  "Gestion des Clients",
    "gestion_commande.php":"Gestion des Commandes",
    "settings.php":        "Parametres"
};


// ============================================
// التنقل داخل الـ iframe عند الضغط على رابط
// ============================================
document.querySelectorAll('.nav-link-item[data-page]').forEach(function(link) {

    link.addEventListener('click', function(e) {
        e.preventDefault(); // منع فتح صفحة جديدة

        var page = this.getAttribute('data-page');

        // 1. تحميل الصفحة في الـ iframe
        document.getElementById('contentFrame').src = page;

        // 2. تغيير العنوان في الـ topbar
        document.getElementById('pageTitle').textContent = pageTitles[page] || page;

        // 3. تغيير الرابط النشط في الـ sidebar
        document.querySelectorAll('.nav-link-item').forEach(function(el) {
            el.classList.remove('active');
        });
        this.classList.add('active');

        // 4. إغلاق الـ sidebar على الموبايل
        closeSidebar();
    });

});


// ============================================
// فتح / إغلاق الـ sidebar (موبايل)
// ============================================
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('overlay').classList.toggle('show');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
}
