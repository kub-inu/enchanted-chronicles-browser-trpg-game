<?php if($expiredView == true): ?>
<h1>Token expiroval</h1>
<p>Ospravedlňujeme sa, ale tvoj overovací token expiroval. Ale ešte je čas to napraviť. Klikne na odkaz a na email ti príde nový overovací odkaz.</p>

<form action="/auth/verify/resend" method="post">
    <input type="hidden" name="_csrf" value="<?=$csrf::token()?>">
    <input type="hidden" name="token" value="<?=$verifyToken?>">
    <input type="submit" value="Znovu odoslať aktivačný odkaz.">
</form>

<?php else: ?>
<h1>Aktivácia účtu</h1>
<p>Tvoj účet sa podarilo aktivovať, môžeš sa prihlásiť.</p>
<a href="/">Prihlásiť sa</a>
<?php endif;?>