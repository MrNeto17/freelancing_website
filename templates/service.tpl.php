<p?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../database/classes/service.class.php');
  require_once(__DIR__ . '/../database/classes/user.class.php');
  require_once(__DIR__ . '/../utils/session.php');
?>
<?php function drawFilterForm(array $categories) { ?>
    <form method="GET" action="/pages/index.php" class="filter-form">
        <div class="filter-group">
            <label for="category">Category:</label>
            <select name="category" id="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?= htmlspecialchars($category->getName()) ?>"
                        <?= ($_GET['category'] ?? '') === $category->getName() ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category->getName()) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="maxPrice">Max Price (€):</label>
            <input type="number" name="maxPrice" id="maxPrice"
                   value="<?= htmlspecialchars($_GET['maxPrice'] ?? '') ?>"
                   min="0" step="0.01" placeholder="No limit">
        </div>

        <div class="filter-group">
            <label for="maxDeliveryTime">Max Delivery Time (days):</label>
            <input type="number" name="maxDeliveryTime" id="maxDeliveryTime"
                   value="<?= htmlspecialchars($_GET['maxDeliveryTime'] ?? '') ?>"
                   min="1" placeholder="No limit">
        </div>

        <button type="submit">Apply Filters</button>
        <a href="/pages/index.php" class="reset-filters">Reset</a>
    </form>
<?php } ?>

<?php function drawServiceCard(Service $service, User $freelancer ,Session $session) { ?>
    <section class="service-card">
        <header class="service-header">
            <h2><?php echo htmlspecialchars($service->getTitle()); ?></h2>
        </header>
        <article class="service-details">
            <p><strong>Freelancer:</strong> <?php echo htmlspecialchars($freelancer->getName()); ?></p>
            <p><?php echo htmlspecialchars($service->getCategory()->getName()) ?> </p>
        </article>
        <footer class="service-footer">
            <p> Price: <?= $service->getPrice() ?></p>
            <p> Time to deliver:  <?php echo htmlspecialchars($service->getDeliveryTime()) ?> days</p>
        </footer>
        <div class="service-actions">
            <?php if ($session->isLoggedIn()) { ?>
                <a href="../pages/service_page.php?service_id=<?php echo $service->getId();?>" class="order">See more</a>
            <?php } else { ?>
                <p>Login to Order</p>
            <?php } ?>
        </div>
    </section>
<?php } ?>


<?php
function drawServices(array $services, Session $session, array $categories) {
    ?>
    <!-- Formulário de Filtros -->
    <form method="GET" action="/pages/index.php" class="filter-form">
        <div class="category-container">
            <select name="category">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categories as $cat) { ?>
                    <option value="<?= htmlspecialchars($cat->getName()) ?>"
                        <?= ($_GET['category'] ?? '') === $cat->getName() ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat->getName()) ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="filter-section">
            <div class="input-row">
                <input type="number" name="maxPrice" placeholder="Preço máximo (€)"
                    value="<?= htmlspecialchars($_GET['maxPrice'] ?? '') ?>" min="0">

                <input type="number" name="maxDeliveryTime" placeholder="Prazo máximo (dias)"
                    value="<?= htmlspecialchars($_GET['maxDeliveryTime'] ?? '') ?>" min="1">
            </div>

            <div class="filter-buttons"> 
                <button type="submit">Filtrar</button>
                <a href="/pages/index.php" class="button">Limpar</a>
            </div>
        </div>
    </form>

    <!-- Lista de Serviços -->
    <section class="services">
        <?php foreach ($services as $service) { ?>
            <?php drawServiceCard($service, $service->getFreelancer(), $session); ?>
        <?php } ?>
    </section>
<?php } ?>


<?php
function drawServiceDetails(Service $service, User $freelancer, Session $session, array $images) { ?>
    <section class="service-container"> <!-- Updated from service-details -->
        <header class="service-header">
            <h2><?= htmlspecialchars($service->getTitle()); ?></h2>
        </header>

        <article class="service-description">
            <p><strong>Description:</strong> <?= htmlspecialchars($service->getDescription()); ?></p>
            <p><strong>Freelancer:</strong> 
            <a href="../pages/profile.php?user_id=<?= $freelancer->getId(); ?>" class="freelancer-name-link">
                <?= htmlspecialchars($freelancer->getName()); ?>
            </a>
            </p>
            <p><strong>Freelancer's email:</strong> <?= htmlspecialchars($freelancer->getEmail()); ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($service->getCategory()->getName()); ?></p>
            <p><strong>Price:</strong> <?= htmlspecialchars($service->getPrice()); ?> €</p>
            <p><strong>Time to deliver:</strong> <?= htmlspecialchars($service->getDeliveryTime()); ?> days</p>
            <?php if ($service->getStatus() === 'completed') { ?>
                <p><strong>Status:</strong> Completed</p>
            <?php } ?>
        </article>

        <section class="service-images">
            <h3>Images:</h3>
            <div class="image-gallery">
                <?php if (count($images) > 0): ?>
                    <?php foreach ($images as $image): ?>
                        <div class="image-item">
                            <img src="<?= $image->getUrl(); ?>" alt="Service Image" class="service-image" onclick="deleteImage('<?= $image->getUrl(); ?>')">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No images available for this service.</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="service-actions">
    <?php if ($service->getFreelancer()->getId() === $session->getId()) { ?>
        <a href="../pages/service_edit_page.php?service_id=<?= $service->getId(); ?>" class="edit">Edit</a>
        <?php if ($service->getStatus() === 'completed') { ?>
            <button class="edit delete-service-btn" onclick="deleteService(<?= $service->getId(); ?>)">Delete</button>
        <?php } else { ?>
            <button class="edit mark-completed" onclick="markAsCompleted(<?= $service->getId(); ?>)">Mark as Completed</button>
        <?php } ?>
    <?php } else { ?>
        <a href="../pages/order_service.php?service_id=<?= $service->getId(); ?>" class="order">Order</a>
    <?php } ?>
</div>
    </section>
<?php } ?>

<?php function drawSearchContainer() { ?>
    <section class="search-container">
        <h1>Search Services</h1>
        <input type="text" id="search-input" placeholder="Search by name..." autocomplete="off">
        <section id="results" class="services"></section>
    </section>
<?php } ?>

<?php function drawCreateServiceForm(Session $session, array $categories) { ?>
    <form action="../actions/action_create_service.php" method="POST" class="service-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <?php foreach ($categories as $category) { ?>
                <option value="<?= htmlspecialchars($category->getName()); ?>"><?= htmlspecialchars($category->getName()); ?></option>
            <?php } ?>
        </select>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" required step="1" min="0">

        <label for="delivery_time">Time to deliver (days):</label>
        <input type="number" name="delivery_time" id="delivery_time" required min="1">

        <button type="submit">Create Service</button>
    </form>
<?php } ?>

<?php function drawEditServiceForm(Service $service, Session $session, array $categories) { ?>
    <form action="../actions/action_edit_service.php" method="POST" class="service-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="service_id" value="<?= $service->getId(); ?>">

        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required value="<?= htmlspecialchars($service->getTitle()); ?>">

        <label for="description">Description:</label>
        <textarea name="description" id="description" required><?= htmlspecialchars($service->getDescription()); ?></textarea>

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <?php foreach ($categories as $category) { 
                $selected = $category->getName() === $service->getCategory()->getName() ? 'selected' : ''; ?>
                <option value="<?= htmlspecialchars($category->getName()); ?>" <?= $selected ?>>
                    <?= htmlspecialchars($category->getName()); ?>
                </option>
            <?php } ?>
        </select>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" required step="1" min="0" value="<?= htmlspecialchars($service->getPrice()); ?>">

        <label for="delivery_time">Time to deliver (days):</label>
        <input type="number" name="delivery_time" id="delivery_time" required min="1" value="<?= htmlspecialchars($service->getDeliveryTime()); ?>">

        <button type="submit">Update Service</button>
    </form>
<?php } ?>
