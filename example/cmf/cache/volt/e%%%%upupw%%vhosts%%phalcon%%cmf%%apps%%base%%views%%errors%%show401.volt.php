
<?php echo $this->getContent(); ?>

<div class="jumbotron">
    <h1>Unauthorized</h1>
    <p>You don't have access to this option. Contact an administrator</p>
    <p><?php echo $this->tag->linkTo(array('index', 'Home', 'class' => 'btn btn-primary')); ?></p>
</div>