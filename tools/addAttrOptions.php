<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script><?php
require_once '../app/Mage.php';
Mage::app('default');
$write = Mage::getSingleton('core/resource')->getConnection('core_write');



$sql ="SELECT attribute_code, attribute_id FROM eav_attribute
WHERE frontend_input = 'select' ORDER BY attribute_code";

// now $write is an instance of Zend_Db_Adapter_Abstract
$readresult=$write->query($sql);

$options='';
while ($row = $readresult->fetch() ) {
    $options .= '<option value="'.$row["attribute_code"].'">'.$row["attribute_code"].'</option>';
}

?>
<form id="attroptions" method="post" action="">
    Attribute:<br />
    <select name="attribute_id" id="attribute_id">
        <?php echo $options; ?>
    </select>

    <br />
    <br />
    New Options:<br />
    <div id="optionsList" style="float:left;">
        <textarea name="options" id="options" rows="40" cols="55"></textarea>
    </div>
    <div id="reAjax" style="float:left; margin-left:20px; border:1px solid black; width: 250px;">
        <b>Results:</b><br /><br />

    </div>
    <div id="clear" style="clear:both;"></div>
    <button class="form-button" onclick="addForm();" type="button">Add</button>
</form>


<script type="text/javascript">
    function getAjax(w, attr, handleData) {
        jQuery.ajax({
            type: "GET",
            url: "attroptions_data.php",
            data: "action=addOption&attr="+attr+"&q="+w,
            success: function(data){
                handleData(data);
            }
        });
    }

    function addForm() {
        var attr_id = jQuery('#attribute_id').val();
        var options = jQuery('#options').val();
        var r = confirm('Are you sure you wish to add the options to the '+attr_id+' attribute?');
        options = options.split(/\n/);
        if(r == true) {
            for (var i = 0; i < options.length; i++) {
                getAjax(options[i], attr_id, function(output){
                    var boxv = jQuery('#reAjax').html();
                    jQuery('#reAjax').html(boxv + output);
                });
            }
        }

    }
</script>