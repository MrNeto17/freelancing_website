<?php declare(strict_types=1); ?>
<?php require_once(__DIR__ . '/../templates/service.tpl.php'); ?>
<?php require_once(__DIR__ . '/../templates/transactions.tpl.php'); ?>
<?php require_once(__DIR__ . '/../templates/review.tpl.php'); ?>

<?php function drawProfile(User $user, array $services, Session $session, array $transactions, array $reviews) { ?>
    <section class="profile-container">
        <h1><?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="user-role"><?= ucfirst(htmlspecialchars($user->getRole())) ?></p>
        <?php if ($user->getRating() !== null) : ?>
            <div class="user-rating">
                Rating: <?= htmlspecialchars(number_format($user->getRating(), 1)) ?>/5.0
            </div>
        <?php endif; ?>

        <!-- Personal Information Section -->
        <div class="info-section">
            <h2 class="section-header">Personal Information</h2>
            
            <div class="info-item">
                <span class="info-label">Username:</span>
                <span class="info-value"><?= htmlspecialchars($user->getUsername(), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($user->getEmail()) ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value">
                    <?= $user->getPhone() ? htmlspecialchars($user->getPhone()) : '<em>Not provided</em>' ?>
                </span>
            </div>

            

            <?php if ($user->getRole() !== "client") : ?>
                <div class="info-item">
                    <span class="info-label">About Me:</span>
                    <span class="info-value">
                        <?= $user->getDescription()
                            ? nl2br(htmlspecialchars($user->getDescription(), ENT_QUOTES, 'UTF-8'))
                            : '<em class="no-description">No description provided</em>' ?>
                    </span>
                </div>
            <?php endif; ?>

                <div class="profile-actions">
                <?php if ($session->isLoggedIn() && $session->getId() === $user->getId()) : ?>
                    <div class="edit-button-container">
                        <a href="profile_edit_page.php" class="edit-profile-btn">Edit Profile</a>

                        <?php if ($user->getRole() !== 'client') : ?>
                            <a href="service_create_page.php" class="edit-profile-btn">Create New Service</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                    <?php if ($session->isLoggedIn() && $session->getId() !== $user->getId()) : ?>
                        <a href="chat_page.php?user_id=<?= $user->getId() ?>" class="message-btn">Send Message</a>
                    <?php endif; ?>
                    <?php if ($session->getRole() === 'admin' && $session->getId() !== $user->getId()){ ?>
                        <button class="message-btn" onclick="ElevateTheUser(<?= $user->getId() ?>)">Elevate to Admin</button>
                    <?php } ?>
                </div>
        </div>

        <?php if ($user->getRole() !== 'client' && !empty($services)) : ?>
                <div class="freelancer-services">
                    <h2>My Services</h2>
                    <div class="services-grid">
                        <?php foreach ($services as $service) : ?>
                            <?php drawServiceCard($service, $user, $session); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($user->getRole() !== 'client' && !empty($reviews)) : ?>
                <div class="reviews-section">
                    <h2>Reviews</h2>
                    <div class="">
                        <?php foreach ($reviews as $review) : ?>
                            <?php drawReviewCard($review) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <!-- Transactions Section -->
        <?php if ($session->isLoggedIn() && $session->getId() === $user->getId()) : ?>
            <div class="info-section"> <!-- Changed from transactions-section to info-section -->
                <h2 class="section-header">My Transactions</h2>
                <?php if (!empty($transactions)) : ?>
                    <?php foreach ($transactions as $transaction) : ?>
                        <?php drawTransaction($transaction); ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="empty-message">No transactions yet</p> <!-- Added class for empty message -->
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
<?php } ?>