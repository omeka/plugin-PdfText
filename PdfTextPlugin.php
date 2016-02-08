<?php
/**
 * PDF Text
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The PDF Text plugin.
 *
 * @package Omeka\Plugins\PdfText
 */
class PdfTextPlugin extends Omeka_Plugin_AbstractPlugin
{
    const ELEMENT_SET_NAME = 'PDF Text';
    const ELEMENT_NAME = 'Text';

    protected $_hooks = array(
        'install',
        'uninstall',
        'initialize',
        'config_form',
        'config',
        'before_save_file',
    );

    protected $_pdfMimeTypes = array(
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'text/x-pdf',
        'text/pdf',
        'applications/vnd.pdf',
    );

    /**
     * @var array  Plugin options
     */
    protected $_options = array(
        // Default to UTF-8
        'pdf_text_encoding' => 'UTF-8',
        'pdf_app_path' => '/usr/local/bin/pdftotext'
    );

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Don't install if the pdftotext command doesn't exist.
        // See: http://stackoverflow.com/questions/592620/check-if-a-program-exists-from-a-bash-script
        if ((int) shell_exec('hash /usr/local/bin/pdftotext 2>&- || echo 1')) {
            throw new Omeka_Plugin_Installer_Exception(__('The pdftotext command-line utility '
            . 'is not installed. pdftotext must be installed to install this plugin. This plugin assumes that the app is installed in /usr/local/bin/.'
            . 'Should that not be the case, update plugins/PdfText/PdfTextPlugin.php with the correct path'));
        }
        // Don't install if a PDF element set already exists.
        if ($this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)) {
            throw new Omeka_Plugin_Installer_Exception(__('An element set by the name "%s" already '
            . 'exists. You must delete that element set to install this plugin.', self::ELEMENT_SET_NAME));
        }
        insert_element_set(
            array('name' => self::ELEMENT_SET_NAME, 'record_type' => 'File'),
            array(array('name' => self::ELEMENT_NAME))
        );
        // set default options
        $this->_installOptions();
    }

    /**
     * Uninstall the plugin
     */
    public function hookUninstall()
    {
        // Delete the PDF element set.
        $this->_db->getTable('ElementSet')->findByName(self::ELEMENT_SET_NAME)->delete();

        $this->_uninstallOptions();
    }

    /**
     * Initialize this plugin.
     */
    public function hookInitialize()
    {
        // Add translation.
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Display the config form.
     */
    public function hookConfigForm()
    {
        echo get_view()->partial(
            'plugins/pdf-text-config-form.php',
            array( 'valid_storage_adapter' => $this->isValidStorageAdapter())
        );

    }

    /**
     * Handle the config form.
     */
    public function hookConfig()
    {
        // set encoding option
        set_option('pdf_text_encoding', $_POST['pdf_text_encoding']);

        // Run the text extraction process if directed to do so.
        if ($_POST['pdf_text_process'] && $this->isValidStorageAdapter()) {
            Zend_Registry::get('bootstrap')->getResource('jobs')
                ->sendLongRunning('PdfTextProcess');
        }
    }

    /**
     * Add the PDF text to the file record.
     *
     * This has a secondary effect of including the text in the search index.
     */
    public function hookBeforeSaveFile($args)
    {
        // Extract text only on file insert.
        if (!$args['insert']) {
            return;
        }
        $file = $args['record'];

        // Get associated Item
        $item = get_record_by_id('Item',$file['item_id']);

        // Ignore non-PDF files.
        if (!in_array($file->mime_type, $this->_pdfMimeTypes)) {
            return;
        }
        // Add the PDF text to the File as well as the Item record.
        $element = $file->getElement(self::ELEMENT_SET_NAME, self::ELEMENT_NAME);
        $elementItemTypeText = $item->getElement("Item Type Metadata", 'Text');

        $text = $this->pdfToText($file->getPath());
        // pdftotext must return a string to be saved to the element_texts table.
        if (is_string($text)) {

            if(!mb_detect_encoding($text, 'UTF-8', true)) {
                $text = utf8_encode($text);
            }

            $file->addTextForElement($element, $text);

            // Add text to Item Type Metadata:Text
            $item->addTextForElement($elementItemTypeText, $text);
            $item->saveElementTexts();
        }
    }

    /**
     * Extract the text from a PDF file.
     *
     * @param string $path
     * @return string
     */
    public function pdfToText($path)
    {
        // get encryption configuration option
        if(get_option('pdf_text_encoding') != '' ) {
            $encString = '-enc' . get_option('pdf_text_encoding');
        } else {
            $encString ='';
        }

        $path = escapeshellarg($path);
        $appPath = get_option('pdf_app_path');

        if(empty($appPath)) {
            $cmd = 'pdftotext';
        } else {
            $cmd = $appPath;
        }
        return shell_exec("$cmd $encString $path -");
    }

    /**
     * Determine if the plugin supports the storage adapter.
     *
     * pdftotext cannot be used on remote files, so only support the default
     * Filesystem adapter, which stores files locally.
     *
     * @return bool
     */
    public function isValidStorageAdapter()
    {
        $storageAdapter = Zend_Registry::get('bootstrap')
            ->getResource('storage')->getAdapter();
        if (!($storageAdapter instanceof Omeka_Storage_Adapter_Filesystem)) {
            return false;
        }
        return true;
    }

    /**
     * Get the PDF MIME types.
     *
     * @return array
     */
    public function getPdfMimeTypes()
    {
        return $this->_pdfMimeTypes;
    }
}
