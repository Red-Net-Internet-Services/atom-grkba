<h1><?php echo __('Manage jobs'); ?></h1>

<nav>
  <ul class="nav nav-pills mb-3 d-flex gap-2">
    <li class="nav-item">
      <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
      <?php if ('all' === $filter) { ?>
        <?php $options['class'] .= ' active'; ?>
        <?php $options['aria-current'] = 'page'; ?>
      <?php } ?>
      <?php echo link_to(__('All jobs'), ['filter' => 'all'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), $options); ?>
    </li>
    <li class="nav-item">
      <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
      <?php if ('active' === $filter) { ?>
        <?php $options['class'] .= ' active'; ?>
        <?php $options['aria-current'] = 'page'; ?>
      <?php } ?>
      <?php echo link_to(__('Active jobs'), ['filter' => 'active'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), $options); ?>
    </li>
    <li class="nav-item">
      <?php $options = ['class' => 'btn atom-btn-white active-primary text-wrap']; ?>
      <?php if ('failed' === $filter) { ?>
        <?php $options['class'] .= ' active'; ?>
        <?php $options['aria-current'] = 'page'; ?>
      <?php } ?>
      <?php echo link_to(__('Failed jobs'), ['filter' => 'failed'] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), $options); ?>
    </li>
  </ul>
</nav>

<div class="tab-content">
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
	<tr>
   	  <style <?php echo __(sfConfig::get('csp_nonce', '')); ?>>
            #job-start-date-head, #job-end-date-head { width: 15% }
            #job-name-head { width: 20% }
	    #job-status-head, #job-user-head { width: 10% }
	    #job-info-head { width: 30% }
          </style>
          <th id="job-start-date-head"><?php echo __('Start date'); ?></th>
          <th id="job-end-date-head"><?php echo __('End date'); ?></th>
          <th id="job-name-head"><?php echo __('Job name'); ?></th>
          <th id="job-status-head"><?php echo __('Job status'); ?></th>
          <th id="job-info-head"><?php echo __('Info'); ?></th>
          <th id="job-user-head"><?php echo __('User'); ?></th>
        </tr>
      </thead>

      <?php $jobs = $pager->getResults(); ?>

      <?php foreach ($jobs as $job) { ?>
        <tr>
          <!-- Creation date -->
          <td><?php echo $job->getCreationDateString(); ?></td>

          <!-- End date -->
          <td><?php echo $job->getCompletionDateString(); ?></td>

          <!-- Job name -->
          <td><?php echo $job; ?></td>

          <!-- Job status -->
          <td class="text-nowrap">
            <?php if (QubitTerm::JOB_STATUS_COMPLETED_ID == $job->statusId) { ?>
              <i aria-hidden="true" class="fa fa-check-square me-1 text-success"></i>
            <?php } elseif (QubitTerm::JOB_STATUS_ERROR_ID == $job->statusId) { ?>
              <i aria-hidden="true" class="fa fa-exclamation-triangle me-1 text-danger"></i>
            <?php } elseif (QubitTerm::JOB_STATUS_IN_PROGRESS_ID == $job->statusId) { ?>
              <i aria-hidden="true" class="fa fa-cogs me-1 text-secondary"></i>
            <?php } ?>

            <?php echo $job->getStatusString(); ?>

            <?php if ($job->getObjectModule() && $job->getObjectSlug()) { ?>
              <a
                href="<?php echo url_for([
                    'module' => $job->getObjectModule(),
                    'slug' => $job->getObjectSlug(),
                ]); ?>"
                class="text-decoration-none ms-1">
                <i class="fa fa-share" aria-hidden="true"></i>
                <span class="visually-hidden">
                  <?php echo __('Go to related resource'); ?>
                </span>
              </a>
            <?php } ?>
          </td>

          <!-- Job notes -->
          <td>
            <?php foreach ($job->getNotes() as $note) { ?>
              <p class="mb-2"><?php echo $note->__toString(); ?></p>
            <?php } ?>
            <?php if (isset($job->downloadPath)) { ?>
              <p class="mb-2">
                <?php echo link_to(__('Download'), public_path($job->downloadPath)); ?>
                (<?php echo hr_filesize(filesize($job->downloadPath)); ?>)
              </p>
            <?php } ?>
            <?php echo link_to(__('Full report'), ['module' => 'jobs', 'action' => 'report', 'id' => $job->id]); ?>
          </td>

          <!-- User who created the job -->
          <td>
            <?php echo esc_entities(QubitJob::getUserString($job)); ?>
          </td>
        </tr>
      <?php } ?>
    </table>
  </div>
</div>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<!-- User tips -->
<?php if ($this->context->user->isAdministrator() && $jobs->count()) { ?>
  <div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle me-2" aria-hidden="true"></i><?php echo __('You may only clear jobs belonging to you.'); ?>
  </div>
<?php } ?>

<?php if (!$jobs->count()) { ?>
  <div class="alert alert-info" role="alert">
    <ul class="list-unstyled m-0">
      <li><?php echo __('There are no jobs to report on.'); ?></li>
    </ul>
  </div>
<?php } ?>

<!-- Action buttons -->
<ul class="actions mb-3 nav gap-2">
  <li>
    <a class="btn atom-btn-outline-light" id="jobs-refresh-button" href="#">
      <i class="fas fa-sync-alt me-1" aria-hidden="true"></i>
      <?php echo __('Refresh'); ?>
    </a>
  </li>
  <li>
    <a class="btn atom-btn-outline-light" href="<?php echo url_for(
        ['module' => 'jobs', 'action' => 'browse', 'autoRefresh' => !$autoRefresh]
        + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()
    ); ?>">
      <i class="fas <?php echo $autoRefresh ? 'fa-check-circle' : 'fa-circle'; ?> me-1" aria-hidden="true"></i>
      <?php echo __('Auto refresh'); ?>
    </a>
  </li>
  <?php if ($jobs->count()) { ?>
    <li>
      <?php echo link_to(__('Export history CSV'), ['module' => 'jobs', 'action' => 'export'],
        ['class' => 'btn atom-btn-outline-light']); ?>
    </li>
    <li>
      <?php echo link_to(__('Clear inactive jobs'), ['module' => 'jobs', 'action' => 'delete'],
        ['class' => 'btn atom-btn-outline-danger']); ?>
    </li>
  <?php } ?>
</ul>

<!-- Refresh after specified interval if auto-refresh enabled -->
<?php if ($autoRefresh) { ?>
  <script <?php echo __(sfConfig::get('csp_nonce', '')); ?>>
    setTimeout(function() {
      window.location.reload(1);
    }, <?php echo $refreshInterval; ?>);
  </script>
<?php } ?>
