<?php include 'includes/header.php'; ?>

<div class="contact-container">
    <div class="contact-card">
        <div class="contact-header">
            <h2>Contact Us 📞</h2>
            <p>Have questions? We'd love to hear from you!</p>
        </div>
        <div class="contact-body">
            <div class="contact-grid">
                <div class="contact-form-section">
                    <h3>Send us a Message</h3>
                    <form method="POST">
                        <div class="contact-form-group">
                            <label>Your Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                        </div>
                        <div class="contact-form-group">
                            <label>Your Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="contact-form-group">
                            <label>Your Message</label>
                            <textarea name="message" class="form-control" placeholder="How can we help you?" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="send" class="btn-contact">Send Message</button>
                    </form>
                    <?php
                    if (isset($_POST['send'])) {
                        echo "<div class='contact-success'>Thank you for contacting us! We'll get back to you soon. 📧</div>";
                    }
                    ?>
                </div>
                <div class="contact-info-section">
                    <h3>Get in Touch</h3>
                    <div class="contact-info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email</strong>
                            <p>vinayak0077830@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Phone</strong>
                            <p>+91-9892373300</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Address</strong>
                            <p>Shop No 2, Jai Laxmi Narayan Chs Ltd, Gopal Krishna Gokhale Road, Mulund East Mumbai - 400081</p>
                        </div>
                    </div>
                    <div class="contact-map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3769.566744449487!2d72.9568!3d19.1715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTnCsDEwJzE4LjYiTiA3MkKwNTcnMjEuNCJF!5e0!3m2!1sen!2sin!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>