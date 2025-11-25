<?php
// REMOVED: password reset handler - file neutralizado per request.
// Original functionality deleted. Keep file as placeholder for compatibility.
http_response_code(410);
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Funcionalidade de redefinição de senha removida.']);
exit;
