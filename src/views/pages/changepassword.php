<?= $render('header', ['loggedUser' => $loggedUser]) ?>
<section class="container main">
    <?= $render('sidebar', ['activeMenu' => 'config']); ?>

    <div class="configform mt-10">
        <form method="POST" action="<?= $base ?>/config/alterarsenha">

            <?php if (!empty($flash)) : ?>
                <div class="flash"><?= $flash ?></div>
            <?php endif; ?>
            <?php if (!empty($flashsuccess)) : ?>
                <div class="flashsuccess"><?= $flashsuccess ?></div>
            <?php endif; ?>

            <label for="newpassword">Nova senha:<br>
                <input class="configinput" type="password" name="newpassword"></label>
            <br><br>

            <label for="confirmpassword">Confirme a nova senha:<br>
                <input class="configinput" type="password" name="confirmpassword"></label>
            <br><br>
            <input class="button" id="submitbtn" type="submit" value="Atualizar dados">
        </form>
    </div>

</section>

<?= $render('footer') ?>