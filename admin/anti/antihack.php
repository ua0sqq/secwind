<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    if (isset($_POST['save']))
    {
        $set['filtr_get'] = isset($_POST['filtr_get']);
        $set['filtr_post'] = isset($_POST['filtr_post']);
        $set['antixss'] = isset($_POST['antixss']); //die(var_dump($_POST));
        Core::save_settings($set);
    }

    $set['title'] = 'Антихак';
    include incDir.'head.php';

    ?>
    <div class="post">Включение фильтров может привести к непредсказуемым последствиям. <br />При включении antiXss, рекомендуется не использовать htmlspecialchars() в text::output()</div>
    <form method = "post">
        <div class="menu_razd">Фильтровать от SQL inj</div>
        <label><input type='checkbox' <?=($set['filtr_get'] ? 'checked="checked"':null)?> name='filtr_get' value='1' /> $_GET</label><br />
        <label><input type='checkbox' <?=($set['filtr_post'] ? 'checked="checked"':null)?> name='filtr_post' value='1' /> $_POST</label><br />
        <label><input type='checkbox' <?=($set['antixss'] ? 'checked="checked"':null)?> name='antixss' value='1' /> Включить antiXss</label><br />
        <input value = "Изменить" name="save" type="submit" />
    </form>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';