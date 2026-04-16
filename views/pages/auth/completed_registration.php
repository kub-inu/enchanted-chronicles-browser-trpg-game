<h1>Dokončenie registrácie</h1>
<p>Platnosť vyprší: <?=$data['expires_at']?></p>
<p>Aby sme dokončili a úspešne overili tvoju registráciu, tak posledným krokom je založenie hernej postavy.</p>

<form method="post" action="/auth/register/verify/<?=$data["token"]?>">  
    <h2>Informácie o postave</h2>

    <label for="character_name">Meno postavy</label><br>
    <input type="text" name="character_name" id="character_name" placeholder="Meno postavy"><br>

    <label for="character_surname">Priezvisko postavy</label><br>
    <input type="text" name="character_surname" id="character_surname" placeholder="Priezvisko postavy"><br>

    <label for="gender">Pohlavie postavy</label><br>
    <select name="character_gender" id="gender">
        <option value="">Vyber pohlavie</option>
        <option value="0">Muž</option>
        <option value="1">Žena</option>
    </select>

    <input type="hidden" name="_csrf" value="<?= $csrf::token() ?>">
    <input type="submit" value="Odoslať">
</form>