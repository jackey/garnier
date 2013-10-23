<form action="index.php?r=photo/uploadimage" enctype="multipart/form-data" method="post" id="uploadimage">
    <?php if ($tmpImage):?>
        <img src="<?php echo Yii::app()->request->baseUrl?><?php echo $tmpImage?>" />
        <p>
            <?php echo CHtml::link("分享微薄", "#")?>
            <?php echo CHtml::link("分享人人", "#")?>
            <?php echo CHtml::link("分享腾讯微薄", "#")?>
        </p>
    <?php else: ?>
        <input type="file" name="image" />
        <input type="submit" value="Submit">
    <?php endif;?>
</form>