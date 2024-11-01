<p>
    <label for="<?php echo $this->get_field_id('title'); ?>">
        <?php echo $this->translate('Title'); ?>:
    </label>
    <input type="text" class="widefat"
           id="<?php echo $this->get_field_id('title'); ?>"
           name="<?php echo $this->get_field_name('title'); ?>"
           value="<?php echo $title; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id('userid'); ?>">
        <?php echo $this->translate('StackOverflow User Number'); ?>:
    </label>
    <input type="text" class="widefat"
           id="<?php echo $this->get_field_id('userid'); ?>"
           name="<?php echo $this->get_field_name('userid'); ?>"
           value="<?php echo $userId; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id('appKey'); ?>">
        <?php echo $this->translate('Request Key'); ?>:
    </label>
    <input type="text" class="widefat"
           id="<?php echo $this->get_field_id('appKey'); ?>"
           name="<?php echo $this->get_field_name('appKey'); ?>"
           value="<?php echo $appKey; ?>" />
    <span class="description">
      <a href="//stackapps.com/questions/2/api-hello-world-code" target="_blank">
        <?php echo $this->translate('About Request keys'); ?></a>.
      Or, <a href="//stackapps.com/apps/register" target="_blank">
        <?php echo $this->translate('Register'); ?></a>
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id('totalAnswers'); ?>">
        <?php echo $this->translate('Number of items to show'); ?>:
    </label>
    <input type="text" size="3"
           id="<?php echo $this->get_field_id('totalAnswers'); ?>"
           name="<?php echo $this->get_field_name('totalAnswers'); ?>"
           value="<?php echo $total; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id('what'); ?>">
        <?php echo $this->translate('What to display'); ?>:
    </label>
    <select class="widefat"
            id="<?php echo $this->get_field_id('what'); ?>"
            name="<?php echo $this->get_field_name('what'); ?>">
        <?php foreach($whatOptions as $whatValue => $whatName): ?>
        <option value="<?php echo $whatValue; ?>"
          <?php echo $whatValue == $what ? 'selected="selected"' : ''; ?>>
          <?php echo $whatName; ?></option>
        <?php endforeach; ?>
    </select>
    <span class="description">
     Select 'Upvotes' to display the N answers that have most recently
     been upvoted. Select 'Answers'  to display the N most recent
     answers you have contributed, regardless whether they
     been upvoted.
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id('sort'); ?>">
        <?php echo $this->translate('Sort by'); ?>:
    </label>
    <select class="widefat"
            id="<?php echo $this->get_field_id('sort'); ?>"
            name="<?php echo $this->get_field_name('sort'); ?>">
        <?php foreach($sortOptions as $sortValue => $sortName): ?>
        <option value="<?php echo $sortValue; ?>"
          <?php echo $sortValue == $sort? 'selected="selected"' : ''; ?>>
          <?php echo $sortName; ?></option>
        <?php endforeach; ?>
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id('cacheActivity'); ?>">
        <?php echo $this->translate('Cache lifetime for activities'); ?>:
    </label>
    <input type="text" size="3"
           id="<?php echo $this->get_field_id('cacheActivity'); ?>"
           name="<?php echo $this->get_field_name('cacheActivity'); ?>"
           value="<?php echo $cacheActivity; ?>" />
    <br/>
    <span class="description">
     The time, in minutes, to cache stackoverflow activity information.
     Larger values will result in quicker page rendering. Smaller values will
     result in fresher information being displayed. If you use zero here,
     the widget will not cache results. (Careful!)
    </span>
</p>
<p>
    <label for="<?php echo $this->get_field_id('cacheUser'); ?>">
        <?php echo $this->translate('Cache lifetime for user info'); ?>:
    </label>
    <input type="text" size="3"
           id="<?php echo $this->get_field_id('cacheUser'); ?>"
           name="<?php echo $this->get_field_name('cacheUser'); ?>"
           value="<?php echo $cacheUser; ?>" />
    <br/>
    <span class="description">
     The time, in minutes, to cache stackoverflow user information.
     Larger values will result in quicker page rendering. Smaller values will
     result in fresher information being displayed. If you use zero here,
     the widget will not cache results. (Careful!)
    </span>
</p>
<!-- donations -->
<div style='margin-top:15px;'>
<p style='font-style: italic;font-weight: bold;color: #26779a;'>
     If you find the Stackoverflow Profile plugin
     to be useful, consider donating. Thanks.</p>
<form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
  <input type='hidden' name='cmd' value='_s-xclick'>
  <input type='hidden' name='hosted_button_id' value='Q43SKHNJEH7PY'>
  <input type='image' name='submit' alt='donate via PayPal' border='0'
    src='https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif'>
  <img alt='' border='0' width='1' height='1
    src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif'>
</form>
</div>
