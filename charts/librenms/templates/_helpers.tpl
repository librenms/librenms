{{- define "librenms.configChecksum" -}}
{{- include (print $.Template.BasePath "/librenms-configmap.yml") . | sha256sum -}}
{{- end -}}