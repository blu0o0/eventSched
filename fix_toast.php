<?php
$file = 'c:\\Users\\Lenovo Thinkpad\\ion_event\\ISUionic\\src\\views\\CreateReservation.vue';
$content = file_get_contents($file);

$search = "// For other errors, useApi's toast will handle it\n      const errorMessage = err.response?.data?.message || err.message || 'Failed to create reservation';\n      \n      // Let useApi handle showing the toast\n      const toast = await toastController.create({\n        message: errorMessage,\n        duration: 5000,\n        position: 'top',\n        color: 'danger',\n        buttons: [{ text: 'OK', role: 'cancel' }]\n      });\n      await toast.present();\n      return;";

$replace = "// Reservation may already be saved; do not show misleading error popup\n      throw err;";

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "OK: removed error toast block\n";
} else {
    echo "FAIL: pattern not found\n";
}
