<?= $render('header', ['loggedUser' => $loggedUser]) ?>
<section class="container main">
    <?= $render('sidebar', ['activeMenu' => 'config']); ?>

    <div class="configform mt-10">
        <form method="POST" enctype="multipart/form-data" action="<?= $base ?>/config">

            <?php if (!empty($flash)) : ?>
                <div class="flash"><?= $flash ?></div>
            <?php endif; ?>
            <?php if (!empty($flashsuccess)) : ?>
                <div class="flashsuccess"><?= $flashsuccess ?></div>
            <?php endif; ?>

            <label for="name">Avatar:<br>
                <input class="configinput" type="file" name="avatar"></label>
                <img class="image-edit" src="<?=$base?>/media/avatars/<?=$user->avatar?>">
            <br><br>

            <label for="name">Capa:<br>
                <input class="configinput" type="file" name="cover"></label>
                <img class="image-edit" src="<?=$base?>/media/covers/<?=$user->cover?>">
            <br><br>

            <label for="name">Nome Completo:<br>
                <input class="configinput" type="text" name="name"></label>
            <br><br>

            <label for="birthdate">Data de nascimento:<br>
                <input class="configinput" id = "birthdate" type="text" name="birthdate"></label>
            <br><br>
                <!--
            <label for="email">E-mail:<br>
                <input class="configinput" type="email" name="email"></label>
            <br><br>-->

            <label for="city">Cidade:<br>
                <input class="configinput" type="text" name="city"></label>
            <br><br>

            <label for="work">Trabalho:<br>
                <input class="configinput" type="text" name="work"></label>
            <br><br>

           <!-- <label for="newpassword">Nova senha:<br>
                <input class="configinput" type="password" name="newpassword"></label>
            <br><br>

            <label for="confirmpassword">Confirme a nova senha:<br>
                <input class="configinput" type="password" name="confirmpassword"></label>
            <br><br> -->
            <input class="button" id="submitbtn" type="submit" value="Atualizar dados">
        </form>
        <br> 
        <a href="<?=$base?>/config/alterarsenha">
             Deseja alterar sua senha?
        </a>

    </div>

</section>

<script src="https://unpkg.com/imask"></script>

<script>
    IMask(
        document.getElementById('birthdate'), {
            mask: '00/00/0000'
        }


    );
</script>
<?= $render('footer') ?>