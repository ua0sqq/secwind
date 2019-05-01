<?php
    
	$set['version'] = '0.4';
	$set['status'] = 'Demo Beta';
	
    include H.'engine/includes/testing.php';
	include H.'engine/includes/chmod_test.php';

	if (isset($err))
	{
	    if (is_array($err))
		{
		    foreach ($err as $key=>$value)
			{
			    echo '<div class="err">'.$value.'</div>';
			}
		}
		else
		    echo '<div class="err">'.$err.'</div>';
	}
	elseif ($step == 2)
	{
	    $_SESSION['install_step']++;
		exit(header('Location: index.php'));
	}
    ?>
	<hr />
    <form>
	    <input name = 'step' value = '<?=$_SESSION['install_step']+1?>' type = 'hidden'/>
	    <input value = 'Далее' type = 'submit' />
	</form>
	<hr />
	
	<?php include_once 'inc/foot.php';