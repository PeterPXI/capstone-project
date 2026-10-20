<?php

declare(strict_types=1);

$pageTitle = 'DealExit | فرص لبيع وشراء المشاريع في مصر';
$searchTerm = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$listings = [];
$databaseError = false;

require __DIR__ . '/config.php';

try {
    $sql = "SELECT l.id, l.title, l.slug, l.description, l.asking_price, l.currency,
                   l.governorate, l.city, l.listing_type, c.name_ar AS category_name,
                   (SELECT image_path FROM listing_images WHERE listing_id = l.id
                    ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1) AS image_path
            FROM listings l
            INNER JOIN categories c ON c.id = l.category_id
            WHERE l.status = 'published'";

    if ($searchTerm !== '') {
        $sql .= ' AND (l.title LIKE ? OR l.description LIKE ? OR l.city LIKE ? OR l.governorate LIKE ? OR c.name_ar LIKE ?)';
    }

    $sql .= ' ORDER BY l.published_at DESC, l.created_at DESC LIMIT 6';
    $statement = $pdo->prepare($sql);
    if ($searchTerm === '') {
        $statement->execute();
    } else {
        $pattern = '%' . $searchTerm . '%';
        $statement->execute([$pattern, $pattern, $pattern, $pattern, $pattern]);
    }
    $listings = $statement->fetchAll();
} catch (PDOException $exception) {
    error_log('DealExit homepage query failed: ' . $exception->getMessage());
    $databaseError = true;
}

require __DIR__ . '/includes/header.php';
?>
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="eyebrow"><span class="eyebrow-dot"></span> منصة انتقال المشاريع في مصر</div>
            <h1>لكل مشروع ناجح، <span>خطوة تالية.</span></h1>
            <p class="hero-lead">قرّب المسافة بين صاحب مشروع يبحث عن فرصته القادمة، ومشترٍ يبحث عن بداية جديدة.</p>
            <div class="hero-actions">
                <a class="btn-solid theme-primary hero-primary" href="#listings">اكتشف الفرص <span aria-hidden="true">←</span></a>
                <a class="btn-outline theme-secondary hero-secondary" href="#how-it-works">كيف تبدأ؟</a>
            </div>
            <div class="hero-note"><span class="note-check" aria-hidden="true">✓</span> بيع مشروعك أو استكشف فرصًا جاهزة للاستحواذ</div>
        </div>

        <div class="hero-art" aria-hidden="true">
            <div class="art-orbit orbit-one"></div>
            <div class="art-orbit orbit-two"></div>
            <div class="art-spark spark-one">✦</div>
            <div class="art-spark spark-two">✧</div>
            <div class="art-main-card">
                <div class="art-main-top"><span class="art-icon"><svg viewBox="0 0 32 32" fill="none"><path d="M5 25V13.5L16 6l11 7.5V25H5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 25v-8h8v8" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></span><span class="art-tag">فرصة جديدة</span></div>
                <div class="art-lines"><i></i><i></i><i></i></div>
                <div class="art-divider"></div>
                <div class="art-bottom"><span class="art-avatar">DE</span><span><b>مشروعك القادم</b><small>يبدأ من هنا</small></span><span class="art-arrow">↗</span></div>
            </div>
            <div class="art-float-card float-top"><span class="float-icon">↗</span><span><b>فرص متنوعة</b><small>في مكان واحد</small></span></div>
            <div class="art-float-card float-bottom"><span class="float-icon float-icon-green">✓</span><span><b>خطوة أوضح</b><small>للبائع والمشتري</small></span></div>
        </div>
    </div>
    <div class="hero-bottom-line" aria-hidden="true"></div>
</section>

<section class="listing-section" id="listings">
    <div class="container">
        <div class="section-heading listing-heading">
            <div>
                <span class="section-kicker">سوق الفرص</span>
                <h2>مشاريع تستحق أن تُكتشف</h2>
                <p>تصفّح الفرص المنشورة وابحث عن النشاط أو المدينة المناسبة لك.</p>
            </div>
            <span class="listing-count"><b><?= count($listings) ?></b> فرصة معروضة</span>
        </div>

        <form class="input-group listing-search" action="<?= $assetPrefix ?>index.php#listings" method="get" role="search">
            <label class="input-group-text search-icon" for="listingSearch">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </label>
            <input class="form-control search-field" id="listingSearch" type="search" name="q" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>" placeholder="ابحث باسم المشروع، النشاط أو المدينة">
            <button class="btn-solid theme-primary search-button" type="submit">ابحث عن فرصة</button>
        </form>

        <?php if ($databaseError): ?>
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">!</span>
                <h3>لم نتمكن من تحميل الفرص الآن</h3>
                <p>حاول تحديث الصفحة بعد قليل.</p>
            </div>
        <?php elseif ($listings === []): ?>
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none"><rect x="7" y="13" width="34" height="27" rx="5" stroke="currentColor" stroke-width="2.2"/><path d="M17 13V9.5A3.5 3.5 0 0 1 20.5 6h7A3.5 3.5 0 0 1 31 9.5V13M7 23h34M20 23v4h8v-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h3><?= $searchTerm !== '' ? 'لا توجد نتائج مطابقة لبحثك' : 'مساحة جديدة لفرص قادمة' ?></h3>
                <p><?= $searchTerm !== '' ? 'جرّب كلمات بحث مختلفة أو ابحث باسم مدينة أخرى.' : 'لا توجد مشاريع منشورة الآن. عُد قريبًا لاكتشاف الفرص الجديدة.' ?></p>
                <?php if ($searchTerm !== ''): ?><a class="empty-link" href="<?= $assetPrefix ?>index.php#listings">عرض كل الفرص <span aria-hidden="true">←</span></a><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4 listing-grid">
                <?php foreach ($listings as $listing): ?>
                <div class="md:col-6 xl:col-4">
                        <article class="card listing-card">
                            <div class="listing-image">
                                <?php if (!empty($listing['image_path'])): ?>
                                    <img class="card-img-top" src="<?= htmlspecialchars($listing['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <span class="listing-placeholder" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none"><path d="M7 38V19l17-12 17 12v19H7Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M18 38V26h12v12M14 20h.01M24 20h.01M34 20h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></span>
                                <?php endif; ?>
                                <span class="listing-category"><?= htmlspecialchars($listing['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="card-body listing-card-body">
                                <h3 class="card-title"><?= htmlspecialchars($listing['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="card-text"><?= htmlspecialchars(mb_strimwidth($listing['description'], 0, 125, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="listing-meta"><span><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M16 8.3c0 4-6 9-6 9s-6-5-6-9a6 6 0 1 1 12 0Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/></svg><?= htmlspecialchars($listing['city'] . '، ' . $listing['governorate'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                <div class="listing-price"><span>السعر المطلوب</span><strong><?= number_format((float) $listing['asking_price'], 0) ?> <small><?= htmlspecialchars($listing['currency'] === 'EGP' ? 'ج.م' : $listing['currency'], ENT_QUOTES, 'UTF-8') ?></small></strong></div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="steps-section" id="how-it-works">
    <div class="container">
        <div class="section-heading steps-heading">
            <div><span class="section-kicker">ببساطة ووضوح</span><h2>من أول فكرة إلى الصفقة</h2><p>ثلاث خطوات تساعدك على بدء رحلتك بثقة.</p></div>
        </div>
        <div class="row g-4 steps-grid">
            <div class="md:col-4"><article class="card step-card"><div class="card-body"><span class="step-number">01</span><span class="step-icon step-icon-blue" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M6 26V13l10-7 10 7v13H6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 26v-8h8v8M10 14h.01M16 14h.01M22 14h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><h3>اعرض مشروعك</h3><p>شارك المعلومات الأساسية عن مشروعك وحدد ما تريد إظهاره للمشترين.</p></div></article></div>
            <div class="md:col-4"><article class="card step-card"><div class="card-body"><span class="step-number">02</span><span class="step-icon step-icon-orange" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><circle cx="14" cy="14" r="8" stroke="currentColor" stroke-width="1.8"/><path d="m20 20 6 6M11 14h6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><h3>اعثر على فرصتك</h3><p>استكشف المشاريع المعروضة وابحث حسب النشاط أو الموقع.</p></div></article></div>
            <div class="md:col-4"><article class="card step-card"><div class="card-body"><span class="step-number">03</span><span class="step-icon step-icon-green" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M7 17.5 13 23l12-14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="1.8"/></svg></span><h3>ابدأ التواصل</h3><p>تواصل مع الطرف المناسب واتخذ خطوتك التالية في الوقت المناسب.</p></div></article></div>
        </div>
    </div>
</section>

<section class="closing-section">
    <div class="container"><div class="closing-card"><div><span class="closing-kicker">فرصتك تبدأ بخطوة</span><h2>جاهز تكتشف ما هو قادم؟</h2><p>تصفّح المشاريع المعروضة وابحث عن فرصة تناسب خطتك.</p></div><a class="btn-solid theme-primary closing-button" href="#listings">تصفّح الفرص <span aria-hidden="true">←</span></a></div></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
