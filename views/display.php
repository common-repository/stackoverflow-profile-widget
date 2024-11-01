<div id="so-answers-widget">
    <h3 class="sb-title widget-title"><?php echo $title; ?></h3>
    <?php if ($total > 0): ?>

    <img class="favicon" src="//stackoverflow.com/favicon.ico" alt="Stack Overflow">
    <span class="so-user-info">
      <a href="<?php echo $soUser->getHtmlUrl(); ?>"
         class="profile-link"><?php echo $soUser->getDisplayName(); ?></a>&nbsp;
      <a href="<?php echo $soUser->getHtmlUrl(); ?>?tab=reputation">
        <span class="reputation-score" title="reputation" dir="ltr"><?php echo $soUser->getReputation(); ?></span>
      </a>
      <span title="<?php echo $badgeCounts['gold']; ?> gold badges">
        <span class="badge1"></span>
        <span class="badgecount"><?php echo $badgeCounts['gold']; ?></span>
      </span>
      <span title="<?php echo $badgeCounts['silver']; ?> silver badges">
        <span class="badge2"></span>
        <span class="badgecount"><?php echo $badgeCounts['silver']; ?></span>
      </span>
      <span title="<?php echo $badgeCounts['bronze']; ?> bronze badges">
        <span class="badge3"></span>
        <span class="badgecount"><?php echo $badgeCounts['bronze']; ?></span>
      </span>
      <span title="<?php echo $soUser->getAnswerCount(); ?> questions answered">
        <span class="badge4">Q</span>
        <span class="badgecount"><?php echo $soUser->getAnswerCount(); ?> </span>
      </span>
    </span>

    <ul class="so-answer-list">
        <?php $c = 0; ?>
        <?php foreach($answers as $answer): ?>
        <?php if ($c < $maxAnswers) : ?>
        <li class="so-answer <?php echo (($c == 0? 'so-first-answer': ((($c+1) == $maxAnswers)? 'so-last-answer' : ''))); ?>">
            <div class="so-answer-votes <?php echo ($answer['is_accepted']? 'answered-accepted' : ''); ?>"><?php echo (int)$answer['score']; ?></div>
            <div class="so-answer-link"><a href="<?php echo $this->getAnswerUrl($answer); ?>">
               <?php echo $answer['title'] ; ?></a></div>
            <!-- htmlspecialchars($answer['title'], ENT_COMPAT, 'UTF-8') -->
            <div class="so-answer-date"><?php echo date('jS F y', $answer['last_activity_date']); ?></div>
            <br style="height:1px;clear:both" />
        </li>
        <?php endif; ?>
        <?php $c++; ?>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
        <p><?php echo $this->translate('No data available.'); ?></p>
    <?php endif; ?>
</div>
