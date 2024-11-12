<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in.</title>
    <link rel="shortcut icon" href="{{ asset('images/Logo.ico') }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="font-sans antialiased">
    <div class="w-1/3 m-auto mt-12">
        <img src="{{ asset('images/Logo.png') }}" alt="logo" class="w-60 m-auto">
        <input placeholder="รหัสพนักงาน" id="userid" class="w-full p-3 border border=gray-200 my-3" type="text">
        <input placeholder="รหัสเข้าคอมพิวเตอร์" id="password" class="w-full p-3 border border=gray-200 my-3"
            type="password">
        <button class="w-full rounded p-3 border border-green-400 text-green-400" onclick="loginFN()">Login</button>
    </div>
</body>
<script>
    async function loginFN() {
        userid = $('#userid').val();
        password = $('#password').val();
        const formData = new FormData();
        formData.append('userid', userid);
        formData.append('password', password);
        const res = await axios.post("{{ env('APP_URL') }}/authcheck", formData);
        console.log(res)
        if (res.data.status == 1) {
            window.location = "{{ env('APP_URL') }}/";
        } else {
            Swal.fire({
                title: "Error",
                text: res.data.text,
                icon: "error",
                confirmButtonColor: "blue"
            });
        }
    }
</script>

</html>
