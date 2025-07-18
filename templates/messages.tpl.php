<?php declare(strict_types=1);

require_once(__DIR__ . '/../utils/utils.php');

?>

<?php function drawContactSection(Session $session, array $contacts, PDO $db) { ?>
    <section id="contacts">
        <h1>Recent contacts</h1>
        <ul>
            <?php
            foreach ($contacts as $contactUser) {
                if ($contactUser instanceof User) {
                    ?>
                    <li class="contact-side" data-user-id="<?= $contactUser->getId() ?>">
                        <a href="../pages/chat_page.php?user_id=<?php echo $contactUser->getId()?>"><?= $contactUser->getName() ?></a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
    </section>
<?php } ?>


<?php function drawChatSection(Session $session, PDO $db, User $otheruser) { ?>
    <section id="chat">
        <div id="contact">
            <a href="../pages/profile.php?user_id=<?= $otheruser->getId() ?>">
                <?php echo $otheruser->getName() ?>
            </a>
        </div>
        <div id="messages">
            <?php
            $currentUser = User::getUserByID($db, $session->getId());
            $messages = Message::getMessages($db, $currentUser, $otheruser, PHP_INT_MAX);
            $messages= array_reverse($messages);
            foreach ($messages as $message) {
                $timeDiff = dateFormat($message->getDatetime());
                if ($message->getSender()->getId() == $currentUser->getId()) {?>
                    <div class="message user1" data-message-id="<?= $message->getId() ?>" onclick="DeleteMessage(<?= $message->getId() ?>)">
                        <p><?= $message->getContent() ?></p>
                        <p><?= $timeDiff ?></p>
                    </div>
                <?php } else { ?>
                    <div class="message user2" data-message-id="<?= $message->getId() ?>">
                        <p><?= $message->getContent() ?></p>
                        <p><?= $timeDiff ?> </p>
                    </div>
                <?php }
            }
            ?>
        </div>
        <form id="writemessage" action="../actions/action_send_message.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->getCSRFToken()) ?>">
            <input type="hidden" id="receiver_id" name="receiver_id" value="<?= $otheruser->getId() ?>">
            <input type="text" id="newmessage" name="content" placeholder="Type your text here...">
            <input type="submit" id="sendbutton" value="Send">
        </form>
    </section>
<?php } ?>