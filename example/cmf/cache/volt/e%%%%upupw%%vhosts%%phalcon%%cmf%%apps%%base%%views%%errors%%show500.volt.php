
<?php echo $this->getContent(); ?>

<div class="jumbotron">
    <h1>Internal Error</h1>
    <p>Something went wrong, if the error continue please contact us</p>
    <p><?php echo $this->tag->linkTo(array('index', 'Home', 'class' => 'btn btn-primary')); ?></p>
</div>