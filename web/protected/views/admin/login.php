<?php $form = $this->beginWidget("CActiveForm")?>

<?php echo $form->errorSummary($model)?>

<div class="row">
	<?php echo $form->label($model, "username")?>
	<?php echo $form->textField($model, "username")?>
</div>

<div class="row">
	<?php echo $form->label($model, "password")?>
	<?php echo $form->passwordField($model, "password")?>
</div>

<div class="submit row">
	<?php echo CHtml::submitButton("Login")?>
</div>

<?php $this->endWidget()?>