<?php
$article=$data;
if ($article):?>
    <div class="post-heading">
        <h1><?= $article->title; ?></h1>
        <h2 class="subheading"><?= $article->sub_title; ?></h2>
        <span class="meta">Posted by
                            <a href="#"><?= $article->authorLogin; ?></a>
            <?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $article->created_at); ?>
            on <?= $date->format('F d, Y'); ?></span>
    </div>
    <hr>
<?php else: ?>
    <p>Article not found!</p>
<?php endif; ?>