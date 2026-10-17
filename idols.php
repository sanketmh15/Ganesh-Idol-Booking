
<?php
require_once 'db.php';
require_once 'includes/functions.php';

$pageTitle = 'Ganpati Idol Collection | Girijatmak Arts';

$result = $conn->query(
    "SELECT * FROM idols WHERE active=1 ORDER BY price ASC"
);

$idols = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $idols[] = $row;
    }
}

require 'includes/header.php';
?>

<section class="catalogue-heading">
    <p class="muted">🙏 Ganpati Bappa Morya 🙏</p>
    <h1>Girijatmak Arts</h1>
    <h2>Our Ganpati Idol Collection</h2>
    <p class="muted">
        Choose your Bappa, explore the collection and book for home delivery.
    </p>
</section>

<section class="catalogue-tools">
    <div class="catalogue-search">
        <label for="idolSearch">Search Ganpati Idols</label>
        <input
            type="search"
            id="idolSearch"
            placeholder="Search by idol name or description..."
        >
    </div>

    <div class="catalogue-filter">
        <label for="priceFilter">Filter by Price</label>
        <select id="priceFilter">
            <option value="all">All Prices</option>
            <option value="under500">Under ₹500</option>
            <option value="500to1000">₹500 – ₹1,000</option>
            <option value="above1000">Above ₹1,000</option>
        </select>
    </div>

    <div class="catalogue-sort">
        <label for="idolSort">Sort By</label>
        <select id="idolSort">
            <option value="low">Price: Low to High</option>
            <option value="high">Price: High to Low</option>
            <option value="name">Name: A to Z</option>
        </select>
    </div>
</section>

<p id="idolCount" class="muted small" aria-live="polite"></p>

<div class="grid" id="idolGrid">
    <?php foreach ($idols as $idol): ?>
        <?php
        $imageName = basename((string)($idol['image'] ?? ''));
        $imagePath = __DIR__ . '/assets/images/' . $imageName;
        ?>

        <article
            class="card idol-card"
            data-name="<?= e($idol['name']) ?>"
            data-description="<?= e($idol['description'] ?? '') ?>"
            data-price="<?= (float)$idol['price'] ?>"
        >
            <div class="idol-image">
                <?php if ($imageName !== '' && is_file($imagePath)): ?>
                    <img
                        src="assets/images/<?= e($imageName) ?>"
                        alt="<?= e($idol['name']) ?>"
                        loading="lazy"
                    >
                <?php else: ?>
                    <div class="image-placeholder">
                        <span>🙏</span>
                        <p>Ganpati Idol</p>
                        <small>Photo coming soon</small>
                    </div>
                <?php endif; ?>
            </div>

            <div class="idol-details">
                <p class="muted small">GIRIJATMAK ARTS</p>
                <h3><?= e($idol['name']) ?></h3>
                <p><?= e($idol['description'] ?? '') ?></p>

                <p class="price">
                    ₹<?= number_format((float)$idol['price'], 2) ?>
                </p>

                <p>
                    Availability:
                    <?php if ((int)$idol['stock'] > 0): ?>
                        <strong>In Stock (<?= (int)$idol['stock'] ?>)</strong>
                    <?php else: ?>
                        <strong>Out of Stock</strong>
                    <?php endif; ?>
                </p>

                <?php if ((int)$idol['stock'] > 0): ?>
                    <a class="btn"
                       href="book.php?id=<?= (int)$idol['id'] ?>">
                        Book Your Bappa →
                    </a>
                <?php else: ?>
                    <span class="pill">Currently Unavailable</span>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<p id="noIdolsMessage" class="notice" hidden>
    No idols match your search. Try changing your search or price filter.
</p>

<script src="assets/js/idol-catalogue.js" defer></script>

<?php require 'includes/footer.php'; ?>
