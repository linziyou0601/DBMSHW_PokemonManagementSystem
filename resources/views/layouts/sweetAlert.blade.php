<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>


<script type="text/javascript">
	@if(Session::get('alert'))
		data = @json(json_decode(Session::pull('alert')['config']));
		Swal.fire({
			title: data['title'],
			html: data['text'],
			width: data['width'],
			heightAuto: data['heightAuto'],
			padding: data['padding'],
			animation: data['animation'],
			showConfirmButton: data['showConfirmButton'],
			showCloseButton: data['showCloseButton'],
			icon: data['icon'],
			allowEscapeKey: data['allowEscapeKey'],
			allowOutsideClick: data['allowOutsideClick'],
			confirmButtonText: data['confirmButtonText'],
			confirmButtonColor: data['confirmButtonColor'],
		});
	@endif
</script>