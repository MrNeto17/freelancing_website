<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/session.php');
require_once(__DIR__ .'/../database/classes/user.class.php');
require_once(__DIR__ .'/../database/classes/service.class.php');
require_once(__DIR__ .'/../database/classes/transactions.class.php');
?>


<?php function drawAdminDashboard(PDO $db ,array $categories, Session $session) { ?>
<section class="admin-dashboard">
    <h2>Admin Dashboard</h2>

    <div class="admin-stats">
      <div class="stat-box">Users: <strong><?= User::countUsers($db) ?></strong></div>
      <div class="stat-box">Services: <strong><?= Service::countServices($db) ?></strong></div>
      <div class="stat-box">Categories: <strong><?= count($categories) ?></strong></div>
      <div class="stat-box">Orders: <strong><?=  Transactions::countTransactions($db)?></strong></div>
    </div>

    <form id="add-category-form" onsubmit="event.preventDefault(); addCategory();">
      <label for="category_name">Add New Category</label>
      <input type="text" id="category_name" name="category_name" placeholder="Enter category name" required>
      <button type="submit">Add Category</button>
    </form>

    <h3>Existing Categories</h3>
    <table class="category-table">
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $category) { ?>
          <tr>
            <td><?= htmlspecialchars($category->getName()) ?></td>
            <td>
            <button onclick="deleteCategory('<?= htmlspecialchars($category->getName()) ?>')">Delete</button>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
</section>


<?php if ($session->getRole() === 'admin') : ?>
    <div class="admin-role-change">
        <h3 class="role-change-title">Change User Role</h3>
        <form action="../actions/action_change_user_role.php" method="POST" class="role-change-form">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCSRFToken()) ?>">
            <div class="form-group">
                <label for="username" class="role-label">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter username" class="role-input" required>
            </div>

            <div class="form-group">
                <label for="new-role" class="role-label">Change Role To:</label>
                <select name="new_role" id="new-role" class="role-select" required>
                    <option value="freelancer">Freelancer</option>
                    <option value="client">Client</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary change-role-btn">Change Role</button>
        </form>
    </div>
<?php endif; ?>

<?php } ?>


