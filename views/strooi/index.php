<h1>Strooiwagens Berekening</h1>

<form method="get" action="/strooiwagens">
    <label for="locatie">Plaats:</label>
    <input type="text" id="locatie" name="locatie" value="<?= htmlspecialchars($locatie) ?>">
    <button type="submit">Bereken</button>
</form>

<p><strong>Plaats:</strong> <?= htmlspecialchars($plaats) ?></p>
<p><strong>Temperatuur:</strong> <?= htmlspecialchars($temperatuur) ?> °C</p>
<p><strong>Weer:</strong> <?= ucfirst(htmlspecialchars($soortWeer)) ?></p>

<h2>Berekening per weg</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>Locatie</th>
        <th>Actie</th>
        <th>Minuten</th>
        <th>Frequentie</th>
    </tr>
    <?php foreach ($berekeningen as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['ID']) ?></td>
            <td><?= htmlspecialchars($b['Naam']) ?></td>
            <td><?= htmlspecialchars($b['Locatie']) ?></td>
            <td><?= htmlspecialchars($b['Actie']) ?></td>
            <td><?= htmlspecialchars($b['Minuten']) ?></td>
            <td><?= htmlspecialchars($b['Frequentie']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<p><strong>Totale strooitijd:</strong> <?= htmlspecialchars($totaalMinuten) ?> minuten</p>
<p><strong>Benodigde strooiwagens:</strong> <?= htmlspecialchars($strooiwagens) ?></p>
