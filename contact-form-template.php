<form id="contact-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
    <p>
        <label for="name">Name:</label>
        <input type="text" id="fullname" name="name" required>
    </p>
    <p>
        <label for="mail">E-Mail-Adresse:</label>
        <input type="email" id="mail" name="email" required>
    </p>
    <p>
        <label for="subject">Betreff:</label>
        <input type="text" id="subject" name="subject" required>
    </p>
    <p>
        <label for="message">Nachricht:</label>
        <textarea id="message" name="message" rows="5" required></textarea>
    </p>
    <p>
        <input type="submit" id="submit" name="submit" value="Nachricht senden">
    </p>
</form>
