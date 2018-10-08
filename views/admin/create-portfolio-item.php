<?php
    require_once '../../core/init.php';
error_reporting(E_ALL);
ini_set('display_errors', 'On');
    if (Input::exists()) {
        $portfolioItem = new PortfolioItem();
        $validate = new Validate();

        $validation = $validate->check($_POST, array(
            // Rules array
            'title' => array(
                'name' => 'Title',
                'required' => true,
            ),
            'description' => array(
                'name' => 'Description',
                'required' => true,
            )
        ));

        if ($validation->passed()) {
            try {
                $portfolioItem->create(array(
                    'title' => Input::get('title'),
                    'description' => Input::get('description'),
                ));

                if (count(Input::getFiles()) > 0) {
                    $images = Input::getFiles();

                    for ($i = 0; $i < count($images['fileUpload']['name']) - 1; $i++) {
                        $tmpFilePath = $_FILES['fileUpload']['tmp_name'][$i];

                        if ($tmpFilePath != "") {
                            //Setup our new file path
                            $newFilePath = "../../uploads/" . md5(date('dd/mm/yy h:i:s')) . $_FILES['fileUpload']['name'][$i];

                            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                                $portfolioItem->createImage(1, md5(date('dd/mm/yy h:i:s')) . $_FILES['fileUpload']['name'][$i], 1);
                            }
                        }
                    }
                }

                Session::flash('success', 'Success!');
                Redirect::to('../index.php');
            } catch (Exception $e) {
                die($e->getMessage());
            }
        } else {
            $errorMessage = '';
            // Validation failed
            $errorMessage .= '
				<div class="alert alert-danger mt-3">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<strong>Please correct the following issues: <br></strong>
				<ul>';
                foreach ($validation->errors() as $error) {
                    $errorMessage .= '<li>'. $error .'</li>';
                }
            $errorMessage.= '</ul>
				</div>';
        }
    }

    require_once '../includes/header.php';
?>
<div class="container">
    <?php if (isset($errorMessage)) {
        echo $errorMessage;
    } ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="title">Title</label>
        <input id="title" type="text" name="title" placeholder="Title..." value="<?= escape(Input::get('title')); ?>" class="form-control squared mb-3">
        <label for="description">Description</label>
        <textarea id="description" class="form-control squared" name="description">
            <?= escape(Input::get('description')); ?>
        </textarea>
        <div id="imageUpload" class="row">
        </div>
        <button type="submit" class="btn btn-secondary mt-3">Create</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
<script>
    $("#imageUpload").spartanMultiImagePicker({
        fieldName:   'fileUpload[]',
    });
    // $("#demo").spartanMultiImagePicker({
    //     rowHeight : '200px',
    //     groupClassName : 'col-6',
    //     placeholderImage: {
    //         image : '../../.../public/images/download.png',
    //         width : '64px'
    //     },
    //     allowedExt: 'png|jpg|jpeg|gif',
    //     dropFileLabel: 'Drop file here',
    // });
</script>