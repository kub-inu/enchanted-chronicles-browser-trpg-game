<h1>Nový účet</h1>

<form action="/auth/register" method="post">
    <input type="text" name="username" id="username" placeholder="username"><br>
    <input type="email" name="email" id="email" placeholder="email"><br>

    <input type="password" name="password" id="password" placeholder="Heslo"><br>
    <input type="password" name="password_check" id="password_check" placeholder="Heslo znovu"><br>

    <input type="checkbox" name="legal" id="legal"><br>

    <input type="hidden" name="_csrf" value="<?= $csrf::token() ?>">
    <input type="submit" value="Registrovať">
</form>