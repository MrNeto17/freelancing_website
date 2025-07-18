<?php function drawEditProfileForm(User $user, Session $session) { ?>
    <section class="edit-profile-form">
        <h1>Edit Profile</h1>
        <form method="POST" action="../actions/action_edit_profile.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCSRFToken(), ENT_QUOTES) ?>">
            <div class="form-group">
                <label for="username">Username*:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user->getUsername()) ?>" required>
            </div>

            <div class="form-group">
                <label for="name">Full Name*:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user->getName()) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email*:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?= htmlspecialchars($user->getPassword()) ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?= $user->getPhone() ? htmlspecialchars($user->getPhone()) : '' ?>">
            </div>

            <?php if ($user->getRole() === 'freelancer') { ?>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?= $user->getDescription() ? htmlspecialchars($user->getDescription()) : '' ?></textarea>
                </div>
            <?php } ?>

            <button type="submit" class="save-btn">Save Changes</button>
            <a href="/pages/profile.php?user_id=<?php echo $user->getId(); ?>" class="cancel-btn">Cancel</a>
        </form>
    </section>
<?php } ?>