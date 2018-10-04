<?php
    require_once '../../core/init.php';

    if (Input::exists()) {
        echo "<pre>" . print_r(Input::getFiles(), TRUE) . "</pre>";
        die();

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
        onAddRow:       function(index){
            console.log(index);
            console.log('add new row');
            console.log($('input[name^="fileUpload"]').value);
        },
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