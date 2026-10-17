
<?php
require_once 'db.php';
require_once 'includes/functions.php';

$pageTitle = 'Girijatmak Arts | Ganpati Idol Collection';

$popular = $conn->query("
    SELECT i.*,
    COALESCE(
        SUM(CASE WHEN b.status <> 'Cancelled'
        THEN b.quantity ELSE 0 END), 0
    ) AS booked_count
    FROM idols i
    LEFT JOIN bookings b ON b.idol_id = i.id
    WHERE i.active = 1
    GROUP BY i.id
    ORDER BY booked_count DESC, i.price ASC
    LIMIT 3
");

require 'includes/header.php';
?>

<section class="hero premium-hero">
    <p class="hero-label">🙏 GANPATI BAPPA MORYA 🙏</p>

    <h1>Bring Bappa's Blessings Home</h1>

    <p>
        Discover your perfect Ganpati idol at Girijatmak Arts.
        Celebrate devotion, tradition and beautiful craftsmanship.
    </p>

    <div class="actions">
        <a class="btn" href="idols.php">Explore Idol Collection →</a>
        <a class="btn secondary" href="my-bookings.php">
            Track My Booking
        </a>
    </div>

    <div class="hero-features">
        <span>🙏 Devotion in Every Idol</span>
        <span>✨ Made for Your Celebration</span>
        <span>🪔 Easy Online Booking</span>
    </div>
</section>

<section class="home-collection">
    <div class="section-heading">
        <p class="muted">OUR COLLECTION</p>
        <h2>Find Your Bappa</h2>
        <p>Explore our popular and budget-friendly Ganpati idols.</p>
    </div>

    <div class="grid">
        <?php while ($idol = $popular->fetch_assoc()): ?>
            <?php
            $imageName = basename((string)($idol['image'] ?? ''));
            $imagePath = __DIR__ . '/assets/images/' . $imageName;
            ?>

            <article class="card idol-card">
                <div class="idol-image">
                    <?php if (
                        $imageName !== '' &&
                        is_file($imagePath)
                    ): ?>
                        <img
                            src="assets/images/<?= e($imageName) ?>"
                            alt="<?= e($idol['name']) ?>"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="image-placeholder">
                            <span>🙏</span>
                            <p>Ganpati Bappa</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="idol-details">
                    <p class="muted small">GIRIJATMAK ARTS</p>

                    <h3><?= e($idol['name']) ?></h3>

                    <p><?= e($idol['description']) ?></p>

                    <p class="price">
                        ₹<?= number_format((float)$idol['price'], 2) ?>
                    </p>

                    <?php if ((int)$idol['stock'] > 0): ?>
                        <p class="stock-available">
                            ✓ Available for booking
                        </p>

                        <a class="btn" href="book.php?id=<?= (int)$idol['id'] ?>">
                            Book Your Bappa →
                        </a>
                    <?php else: ?>
                        <p class="stock-unavailable">Currently unavailable</p>
                        <span class="pill">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>

    <div class="collection-action">
        <a class="btn secondary" href="idols.php">
            View All Idols
        </a>
    </div>
</section>

<section class="devotion-banner">
    <p>॥ श्री गणेशाय नमः ॥</p>
    <h2>A Celebration Filled With Devotion</h2>
    <p>
        Let every corner of your home welcome Ganpati Bappa
        with love, faith and happiness.
    </p>
</section>

<?php require 'includes/footer.php'; ?>
