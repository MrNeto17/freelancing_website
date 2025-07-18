<?php
function drawRegisterForm(Session $session ): void { ?>
    <div class="register-form">
    <h1>Register</h1>
    <form method="POST" action="/actions/action_register.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCSRFToken(), ENT_QUOTES) ?>">

        <div class="form-group">
            <label for="email">Email*:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password*:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="name">Name*:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone">
        </div>

        <div class="form-group">
            <label for="role">Description*:</label>
            <select id="role" name="role" required>
                <option value="client">Client</option>
                <option value="freelancer">Freelancer</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit">Register</button>
    </form>
</div>
<?php } ?>