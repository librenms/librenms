<h3>MIB Uploader</h3>
<br />
<form method="post" action="/snmptrapmanager/?mode=upload" enctype="multipart/form-data" style="margin-left: 20px">
     <input type="file" name="form_upload_mib_mibs[]" id="form_upload_mib_mibs" multiple /><br />
     <input type="submit" name="form_upload_mib_submit" />
     <label><input type="checkbox" name="form_upload_mib_update_version" id="form_upload_mib_update_version" checked /> <?php echo _("Update mibs to the new uploaded version"); ?></label>
</form>
