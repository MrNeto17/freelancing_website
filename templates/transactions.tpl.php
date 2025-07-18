<?php function drawTransactionForm(Service $service, Session $session): void { ?>
    <div class="payment-container">
    <form id="paymentForm" data-price="<?php echo htmlspecialchars($service->getPrice()); ?>" action="../actions/action_order.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCSRFToken(), ENT_QUOTES) ?>">
        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service->getId()); ?>">

          <label for="email">Email*:</label>
          <input type="email" id="email" name="email" required>

          <label for="firstName">First Name*:</label>
          <input type="text" id="firstName" name="firstName" required>

          <label for="lastName">Last Name*:</label>
          <input type="text" id="lastName" name="lastName" required>

          <label for="currency">Currency*:</label>
          <select id="currency" name="currency" required>
              <option value="EUR">EUR (€)</option>
              <option value="USD">USD ($)</option>
              <option value="GBP">GBP (£)</option>
              <option value="JPY">JPY (¥)</option>
          </select>

          <div id="convertedAmountContainer" class="payment-field active" style="margin-top: 1em;">
            <label for="convertedAmount">Converted Price:</label>
            <input type="text" id="convertedAmount" placeholder="<?php echo $service->getPrice(); ?>" readonly>
          </div>

          <input type="hidden" name="subtotal" value="<?php echo $service->getPrice(); ?>">

          <label for="paymentMethod">Payment Method*:</label>
          <select id="paymentMethod" name="paymentMethod" required onchange="togglePaymentFields()">
              <option value="credit_card">Credit Card</option>
              <option value="paypal">PayPal</option>
              <option value="bank_transfer">Bank Transfer</option>
          </select>

          

          <div id="creditCardFields">
              <label for="cardNumber">Card Number*:</label>
              <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456">
          </div>

          <div id="paypalFields" style="display: none;">
              <label for="paypalEmail">PayPal Email*:</label>
              <input type="email" id="paypalEmail" name="paypalEmail" placeholder="your-email@paypal.com">
          </div>

          <div id="bankTransferFields" style="display: none;">
              <label for="iban">IBAN*:</label>
              <input type="text" id="iban" name="iban" placeholder="DE89 3704 0044 0532 0130 00">
          </div>

            <button class="pay-now">Pay Now</button>
          </form>
    </div>
<?php } ?>

<?php function drawTransaction(Transactions $transaction): void { ?>
    <div class="service-card">
    <div class="card-header">
        <h3>Transaction #<?= htmlspecialchars((string)$transaction->getId()) ?></h3>
        <div class="freelancer">
            <?= htmlspecialchars($transaction->getService()->getFreelancer()->getName()) ?>
            (<?= htmlspecialchars($transaction->getService()->getFreelancer()->getEmail()) ?>)
        </div>
    </div>

    <div class="service-details">
        <p><strong>Service:</strong> <?= htmlspecialchars($transaction->getService()->getTitle()) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($transaction->getCreatedAt() ?? 'N/A') ?></p>
        <p><strong>Amount:</strong> $<?= number_format($transaction->getSubtotal(), 2) ?></p>
        <p><strong>Status:</strong> <span class="status-<?= strtolower($transaction->getService()->getStatus()) ?>">
            <?= htmlspecialchars($transaction->getService()->getStatus()) ?>
        </span></p>
    </div>

    <div class="service-actions">
        <a href="../pages/service_page.php?service_id=<?= htmlspecialchars($transaction->getService()->getId()) ?>" class="order">Go to Service</a>
        <?php if ($transaction->getService()->getStatus() === 'completed'){ ?>
            <a href="../pages/review_page.php?service_id=<?= htmlspecialchars($transaction->getService()->getId()) ?>" class="order">Leave a Review</a>
        <?php } ?>
    </div>
</div>

<?php } ?>
