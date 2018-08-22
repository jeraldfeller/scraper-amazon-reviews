<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Amazon Review Scraper</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 text-center">
            <h1>Amazon Review Scraper</h1>
        </div>
        <div class="col-md-12 text-center">
            <a class="btn btn-success btn-lg" href="export.php?action=reviews">Export Data</a>
            <a class="btn btn-success btn-lg" href="export.php?action=inputs">Export Inputs</a>
        </div>
        <div class="col-md-12 text-center">
            <hr>
            <form id="form">
                <input type="file" name="importFile" id="import-file-holder">
                <button type="submit" class="btn btn-success btn-md" id="submitBtn">Import</button>
            </form>
            <hr>
        </div>
    </div>
</div>

<script type="text/javascript" src="assets/js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
      $('#form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $.ajax({
          url: 'Controller/review.php?action=import',
          type: 'POST',
          xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            $('#submitBtn').attr('disabled', true).html('Importing....');
            return myXhr;
          },
          success: function (data) {
            if(data == 1){
              alert('Asins successfully imported.');
            }else{
              alert('Oops somehting went wrong, please try again.')
            }
            $('#submitBtn').removeAttr('disabled').html('Import');
          },
          data: formData,
          cache: false,
          contentType: false,
          processData: false
        });
        return false;
      });
    });
</script>
</body>

</html>