<h1>Zabudnuté heslo</h1>

<?php if($showResetPasswordForm == false): ?>
<p>Zadaj email pre obnovu hesla.</p>

<form action="/reset-password" method="post">

    <label for="email">Email</label>
    <input type="email" name="email" id="email">

    <input type="hidden" name="_csrf" id="_csrf" value="<?=$csrf::token()?>">
    <input type="submit" value="Odoslať kód">
</form>
<?php else: ?>
<form action="/auth/password/reset" method="post">

    <label for="password">Nové heslo</label>
    <input type="password" name="password" id="password">
    
    <label for="password_check">Kontrola hesla</label>
    <input type="password" name="password_check" id="password_check">

    <input type="hidden" name="token" id="token" value="<?=$token?>">
    <input type="hidden" name="_csrf" id="_csrf" value="<?=$csrf::token()?>">
    <input type="submit" value="Vytvoriť nové heslo">
</form>
<?php endif; ?>