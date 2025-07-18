<?php function drawReviewForm(Session $session, Service $service): void { ?>
    <form action="../actions/action_submit_review.php" method="post" class="review-form">
        <input type="hidden" name="service_id" value="<?= htmlspecialchars($service->getId()) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCSRFToken()) ?>">

        <div class="form-group">
            <label for="rating">Rating</label>
            <select name="rating" id="rating" required>
                <option value="" disabled selected>Select a rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="comment">Comment</label>
            <textarea name="comment" id="comment" rows="4" required></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Submit Review</button>
            <a href="../pages/service_page.php?service_id=<?= $service->getId() ?>" class="btn-cancel">Cancel</a>
        </div>
    </form>
<?php } ?>


<?php function drawReviewCard(Review $review): void { 
    $createdAt = $review->getCreatedAt() ?? '';
    $timestamp = strtotime($createdAt);
    $isoDate = $timestamp ? date('Y-m-d', $timestamp) : '';
    $displayDate = $timestamp ? date('F j, Y', $timestamp) : 'Date unavailable';
?>
    <article class="review-card" aria-label="Review by <?= htmlspecialchars($review->getUser()?->getUsername() ?? 'Anonymous') ?>">
        <div class="review-header">
            <strong><?= htmlspecialchars($review->getUser()?->getUsername() ?? 'Anonymous') ?></strong>
            <time class="review-date" datetime="<?= htmlspecialchars($isoDate) ?>">
                <?= htmlspecialchars($displayDate) ?>
            </time>
        </div>
        <div class="review-rating" aria-label="Rating: <?= (int)$review->getRating() ?> out of 5">
            <?php 
            $rating = min(max((int)$review->getRating(), 0), 5);
            echo str_repeat('<span class="star-filled">★</span>', $rating);
            echo str_repeat('<span class="star-empty">☆</span>', 5 - $rating);
            ?>
        </div>
        <div class="review-comment">
            <?= nl2br(htmlspecialchars($review->getComment() ?? '')) ?>
        </div>
    </article>
<?php } ?>