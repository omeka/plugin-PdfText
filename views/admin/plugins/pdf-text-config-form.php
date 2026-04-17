<div class="field">
    <div id="pdf_text_process_label" class="two columns alpha">
        <label for="pdf_text_process"><?php echo __('Process existing PDF files'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
        <?php 
        echo __(
            'This plugin enables searching on PDF files by extracting '
            . 'their texts and saving them to their file records. This '
            . 'normally happens automatically, but there are times when '
            . 'you\'ll want to extract text from all PDF files that '
            . 'already exist in your Omeka repository, like when first '
            . 'installing this plugin. Check the box below and submit '
            . 'this form to run the text extraction process, which may '
            . 'take some time to finish.');
        ?>
        </p>
        <?php if ($this->valid_storage_adapter): ?>
        <?php echo $this->formCheckbox('pdf_text_process'); ?>
        <?php else: ?>
        <p class="error">
        <?php
        echo __(
            'This plugin does not support processing of PDF files that '
            . 'are stored remotely. Processing existing PDF files has been '
            . 'disabled.'
        );
        ?>
        </p>
        <?php endif; ?>
    </div>
</div>

<h3><?php echo __('Extract PDF Metadata'); ?></h3>
<p><?php echo __(
    'Check the boxes below to extract metadata fields from each '
    . 'PDF\'s embedded information using pdfinfo and save them to the '
    . 'corresponding Dublin Core fields. Once enabled, this happens '
    . 'automatically for new uploads. To process existing PDF files, '
    . 'run the process above.'); ?></p>
<?php foreach ($this->pdfinfo_mappings as $mapping): ?>
<div class="field">
    <div id="<?php echo $mapping['option']; ?>_label" class="two columns alpha">
        <label for="<?php echo $mapping['option']; ?>"><?php echo __($mapping['label']); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo $this->formCheckbox($mapping['option'], null, ['checked' => $this->pdfinfo_options[$mapping['option']]]); ?>
    </div>
</div>
<?php endforeach; ?>
