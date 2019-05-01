<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Антиспам';
    include incDir.'head.php';

    function __save($array = false)
    {
        if ($array)
            file_put_contents(H.'engine/files/data/antispam.db', serialize($array));
        return unserialize(file_get_contents(H . 'engine/files/data/antispam.db'));
    }

    $antispam = file_exists(H . 'engine/files/data/antispam.db') ? __save() : array();

    if (isset($_POST['add_save']))
    {
        $antispam = __save(array_merge($antispam, array($_POST['key'] => $_POST['value'])));
    }

    elseif (isset($_POST['del']) && !empty($_POST['delete']))
    {
        foreach($_POST['delete'] as $key)
        {
            unset($antispam[$key]);
        }
        $antispam = __save($antispam);
    }

    elseif (isset($_POST['save']))
    {
       $antispam =  __save(array_combine(array_values($_POST['spam']['keys']), array_values($_POST['spam']['values'])));
    }

    echo '<div class="post">Нагрузка от антиспама очень маленькая, потому что не хранит данные в бд и не использует регулярные выражения</div><form method="post">';

    if (isset($_POST['add']))
    {
        echo '<input type="text" name="key" value=""/> заменить на <input type="text" name="value" value=""/> <input type="submit" name="add_save" value="+"/><br />';
    }

    foreach($antispam as $key => $value)
    {
        echo '<input type="checkbox" name="delete[]" value="'.htmlspecialchars($key).'"/> <input type="text" name="spam[keys][]" value="'.htmlspecialchars($key).'"/> на <input type="text" name="spam[values][]" value="'.htmlspecialchars($value).'"/> <br />';
    }
   

    ?>
    <input type="submit" name="del" value="Удалить"/> &nbsp; &nbsp; <input type="submit" name="save" value="Сохранить"/> &nbsp; &nbsp; <input type="submit" name="add" value="Добавить"/>
    </form>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';