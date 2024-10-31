<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PR9 LINEID CHECK</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/axios@1.6.7/dist/axios.min.js"></script>
    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body>
    <div class="w-full md:w-11/12 m-auto">
        <div class="flex">
            <div>
                <img src="{{ asset('images/side.png') }}" alt="logo" class="h-16 my-3">
            </div>
            <div class="flex-1 text-end my-3 text-xl font-bold">PR9 LineID Check</div>
        </div>
        <form method="GET" action="{{ env('APP_URL') }}/filter" id="filterForm">
            <div class="flex gap-2 shadow p-3 align-middle">
                @csrf
                <div class="inline-block align-middle">Admit Date: </div>
                <input class="w-full p-3 border-2 border-gray-400 rounded" name="date" type="date"
                    value="{{ $filter->date }}">
                <div class="inline-block align-middle">Ward: </div>
                <select class="w-full p-3 border-2 border-gray-400 rounded" name="ward">
                    <option value="">Please Select</option>
                    @foreach ($ward as $item)
                        <option value="{{ $item->Code }}" @if ($filter->ward == $item->Code) selected @endif>
                            {{ $item->name }}</option>
                    @endforeach
                </select>
                <div>HN: </div>
                <input class="w-full p-3 border-2 border-gray-400 rounded" type="text" name="hn"
                    value="{{ $filter->hn }}">
                <div>Status: </div>
                <select class="w-full p-3 border-2 border-gray-400 rounded" name="status">
                    <option value="">Please Select</option>
                    <option value="false" @if ($filter->status == 'false') selected @endif>Wait</option>
                    <option value="true" @if ($filter->status == 'true') selected @endif>Success</option>
                    <option value="denail" @if ($filter->status == 'denail') selected @endif>Denied</option>
                </select>
                <button class="p-3 border-2 text-green-600 border-green-600 w-full rounded" type="button"
                    onclick="filterForm()">Filter</button>
                <div>
                    <button class="p-3 text-red-600 underline underline-offset-2" type="button"
                        onclick="clearFn()">Clear</button>
                </div>
            </div>
        </form>
        <table class="w-full table my-6">
            <thead>
                <tr class="border border-gray-400 border-collapse bg-gray-100 text-center">
                    <td class="p-3">#</td>
                    <td class="border-x border-gray-400">AN</td>
                    <td class="border-x border-gray-400">HN</td>
                    <td class="border-x border-gray-400">Bed</td>
                    <td class="border-x border-gray-400">Gender</td>
                    <td class="border-x border-gray-400">Name</td>
                    <td class="border-x border-gray-400">Age</td>
                    <td class="border-x border-gray-400">Ward</td>
                    <td class="border-x border-gray-400">Right</td>
                    <td class="border-x border-gray-400">ARcode</td>
                    <td class="border-x border-gray-400">LineID</td>
                    <td class="border-x border-gray-400">Denied</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $item)
                    <tr class="border border-gray-400 border-collapse">
                        <td class="p-3 text-center">{{ $i + 1 }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['AN'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['HN'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['Bed'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['Gender'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['Name'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['Age'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['Ward'] }}</td>
                        <td class="p-3 border-x border-gray-400">{{ $item['Right'] }}</td>
                        <td class="p-3 border-x border-gray-400">
                            <div>
                                @foreach ($item['ARcode'] as $ar)
                                    <div class="w-full">{{ $ar }}</div>
                                @endforeach
                            </div>
                        </td>
                        @if ($item['Line'] == 1)
                            <td class="border-x border-gray-400 bg-green-600 text-center text-white">TRUE</td>
                        @elseif($item['Line'] == 0)
                            <td class="border-x border-gray-400 bg-red-600 text-center text-white">FALSE</td>
                            <td class="p-3 border-x border-x-gray-400 text-center">
                                <button onclick="denailFN('{{ $item['HN'] }}')"
                                    class="w-full p-3 border-2 text-blue-600 border-blue-600 rounded">Denied</button>
                            </td>
                        @else
                            <td class="p-3 border-x border-gray-400 bg-gray-500 text-white text-center">Denied</td>
                            <td class="p-3 border-x border-gray-400">{{ $item['Memo'] }}</td>
                        @endif

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
<script>
    function filterForm() {
        Swal.fire({
            title: "Please, Wait.",
            icon: "info",
            allowOutsideClick: false,
            showConfirmButton: false
        });

        document.getElementById("filterForm").submit();
    }

    function denailFN(hn) {
        Swal.fire({
            title: "Confirm, denail HN: " + hn + " ?",
            icon: "warning",
            input: "text",
            showCancelButton: true,
            confirmButtonColor: "gray",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, denail it!"
        }).then(async (result) => {
            if (result.isConfirmed) {
                if (result.value == '') {
                    Swal.fire({
                        title: "Error!",
                        text: "Please, fill reason.",
                        icon: "error"
                    })
                } else {
                    const formData = new FormData()
                    formData.append('hn', hn)
                    formData.append('reason', result.value)
                    const res = await axios.post("{{ env('APP_URL') }}" + "/denail", formData, {
                        "Content-Type": "multipart/form-data"
                    }).then((res) => {
                        if (res.data.status == 'success') {
                            Swal.fire({
                                title: "Denail!",
                                text: "HN : " + hn + " has been updated.",
                                icon: "success",
                                confirmButtonText: 'Confirm',
                                confirmButtonColor: 'green'
                            }).then(function(isConfirmed) {
                                if (isConfirmed) {
                                    window.location.reload()
                                }
                            })
                        }
                    })
                }
            }
        });
    }

    function clearFn() {
        window.location.replace('{{ env('APP_URL') }}/')
    }
</script>

</html>
