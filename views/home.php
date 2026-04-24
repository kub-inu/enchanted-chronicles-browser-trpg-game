<?php if($auth::check()): ?>
<?php 
    $user = $auth::userForView();    
?>    
<h1>Si prihlásený ako, <?=$user['username']?>.</h1>


<?php if ($auth::hasRole('admin')): ?>
    <a href="/admin">Admin panel</a>
<?php endif; ?>

<form action="/auth/logout" method="post">
    <input type="hidden" name="_csrf" value="<?= $csrf::token() ?>">
    <button type="submit">Odhlásiť</button>
</form>

<?php else: ?>

<?php 
    if($expired){
        echo $expired . '<br>';
    }    
?>

<form action="/auth/login" method="post">
    <input type="text" placeholder="Username" name="username"><br>
    <input type="password" placeholder="Psw" name="password"><br>
    <input type="hidden" name="_csrf" value="<?= $csrf::token() ?>">
    <input type="submit" value="Prihlásiť sa">
</form>

<a href="/auth/register">Registrácia</a>
<a href="/reset-password">Zabudol som heslo</a>

<?php endif; ?>