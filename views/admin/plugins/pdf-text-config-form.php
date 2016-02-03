<div class="field">
    <div id="pdf_text_encoding_label" class="two columns alpha">
        <label for="pdf_text_encoding"><?php echo __('Default Encoding for PDFToText'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo $this->formText('pdf_text_encoding', get_option('pdf_text_encoding')); ?>
        <p class="explanation">
            <?php
            echo __(
                'Set the encoding to be used by the <code>pdftotext</code> command line utility. '
                . 'It is very important that it matches the encoding you have set for your database. <br />'
                . 'The default is most likely <code>UTF-8</code>.');
            ?>
        </p>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('pdf_app_path', __('Path to PDFtoText app')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $this->formText('pdf_app_path', get_option('pdf_app_path'), array('placeholder' => 'e.g. /usr/local/bin/pdftotext')); ?>

            <p class="explanation">
                <?php
                echo __('On some server environments Apache is not aware of the path to the PDFtoText app. '
                    . 'In order to avoid processing errors, you can specify the full path to the app.') . '<br />'
                    . '<b>'. __('Important:') . '</b>' . __(' Please make sure full path ends in ') .  '<em>pdftotext</em>.';
                ?>
            </p>
        </div>
    </div>
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
