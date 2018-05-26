<?php
if (!defined('_PS_VERSION_'))
    exit;

include_once(dirname(__FILE__) . '/SwExtraImage.php');

class swextraprodimg extends Module {

    public function __construct() {
        $this->name = 'swextraprodimg';
        $this->tab = 'administration';
        $this->version = '1.6.1';
        $this->author = 'Sebastian Weszler';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('SW Extra Product Images');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    }

    public function install() {
        if (!parent::install() || !$this->installDB() || !$this->registerHook('hookDisplayHeader') || !$this->registerHook('displayRightColumnProduct'))
            return false;

        return true;
    }

    public function uninstall() {
        if (!parent::uninstall() || !$this->uninstallDB())
            return false;

        return true;
    }

    public function hookHeader($params) {
        if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'product')
            return;
        $this->context->controller->addCss($this->_path . 'css/swextraprodimg.css');
    }

    public function ajaxProcess() {

        if (!isset($_POST['action']) && !isset($_POST['ajax']))
            return false;

        $return = array();
        switch ($_POST['action']) {
            case 'add-new':
                $result = $this->ajaxNewImage();
                $return['status'] = $result['status'];
                $return['object'] = $result['object'];
                $return['new_list'] = SwExtraImage::getAll();
                echo json_encode($return);
                die();
                break;
            case 'update':
                $return['status'] = $this->ajaxUpdateImage();
                $return['new_list'] = SwExtraImage::getAll();
                echo json_encode($return);
                die();
                break;
            case 'get-all':
                $return['status'] = 'ok';
                $return['new_list'] = SwExtraImage::getAll();
                echo json_encode($return);
                die();
                break;
            case 'upload':
                $return['status'] = $this->handleUpload();
                $return['new_list'] = SwExtraImage::getAll();
                echo json_encode($return);
                die();
                break;
            case 'delete':
                $return['status'] = $this->ajaxDeleteImage();
                $return['new_list'] = SwExtraImage::getAll();
                echo json_encode($return);
                die();
                break;
        }
    }

    public function installDB() {
        $sql = 'CREATE TABLE `' . _DB_PREFIX_ . 'swextraprodimg` (
            `id_image` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` TEXT,
            `url` TEXT,
            `src` TEXT,
            `alt` TEXT,
            `date_add` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_update` DATETIME NOT NULL,
            PRIMARY KEY (`id_image`)
        ) DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    public function uninstallDB() {
        return Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'swextraprodimg`;');
    }

    public function hookDisplayRightColumnProduct($params) {
        if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'product')
            return;

        $extraimages = SwExtraImage::getByProductId(Tools::getValue('id_product'));
        $product_id = Tools::getValue('id_product');
        if ($product_id) {
            $product = new Product($product_id);
            $manufacturer = new Manufacturer($product->id_manufacturer);
            $this->smarty->assign(array(
                'product' => $product,
                'manufacturer' => $manufacturer
            ));
        }

        $this->smarty->assign(array(
            'extraimages' => $extraimages
        ));

        return $this->display(__FILE__, 'swextraprodimg.tpl');
    }

    public function ajaxNewImage() {
        if (!isset($_POST['object']))
            return false;

        $image = new SwExtraImage();
        $image->id_product = (int) $_POST['object']['id_product'];
        $image->alt = $_POST['object']['alt'];
        $image->date_update = date('Y-m-d H:i:s');
        return array('status' => $image->add(), 'object' => $image);
    }

    public function ajaxUpdateImage() {
        if (!isset($_POST['object']))
            return false;

        $image = new SwExtraImage((int) $_POST['object']['id_image']);
        $image->id_product = $_POST['object']['id_product'];
        $image->alt = $_POST['object']['alt'];
        $image->src = $_POST['object']['src'];
        $image->date_update = date('Y-m-d H:i:s');
        return array('action' => 'update', 'status' => $image->save());
    }

    public function ajaxDeleteImage() {
        if (!isset($_POST['id_image']))
            return false;

        $image = new SwExtraImage((int) $_POST['id_image']);

        $return = $image->delete();
        if ($return)
            unlink(_PS_ROOT_DIR_ . '/' . $image->src);

        return $return;
    }

    public function handleUpload() {
        if (isset($_FILES['file']) && isset($_POST['id_image'])) {
            $fileExt = end(explode('.', $_FILES['file']['name']));
            $dirPath = '/uploads/' . $_POST['id_image'] . '.' . $fileExt;
            move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . $dirPath);
            $image = new SwExtraImage((int) $_POST['id_image']);
            $image->src = 'modules/swextraprodimg' . $dirPath;
            $image->date_update = date('Y-m-d H:i:s');
            $image->save();
        }
    }

    public function getContent() {
        $this->ajaxProcess();
        ob_start();
        ?>

        <div class="panel">
            <script>
                var ps_module = '<?php echo $this->l('SW Extra Product Image'); ?>';
                var ps_img_list = <?php echo json_encode(SwExtraImage::getAll()); ?>;
                var RURI = location.href;
                var base_uri = '<?php echo __PS_BASE_URI__ ?>';
            </script>

            <div  id="app"></div>
            <script type="text/javascript" src="<?php echo __PS_BASE_URI__ . '/modules/swextraprodimg/swextraprodimg/js/admin.js'; ?>"></script>
        </div>
        <?php
        return ob_get_clean();
    }

}
