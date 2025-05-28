@extends('home.templates.index')

@section('page-content')
<section id="home-section" class="ftco-section">
    <div class="container mt-4">
        <div>
            <div class="card text-center" style="background-color: #333333;">
                <p style="color: #A38758;" class="m-auto py-3">
                    <img src="{{ asset('foto/1b.png') }}" href="{{ url('home') }}" width="20"> Detail Informasi
                    <img src="{{ asset('foto/line.png') }}" href="{{ url('home') }}" width="20">
                    <img src="{{ asset('foto/2b.png') }}" href="{{ url('home') }}" width="20"> Pembayaran
                    <img src="{{ asset('foto/line.png') }}" href="{{ url('home') }}" width="20">
                    <img src="{{ asset('foto/3a.png') }}" href="{{ url('home') }}" width="20"> Konfirmasi
                </p>
            </div>
        </div>
        <div class="mt-5">
            <h1 style="color: black; font-weight:bold;">Pembayaran</h1>
        </div>
        <form method="post" enctype="multipart/form-data" action="{{ url('home/pembayaransimpan') }}">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card py-2 mb-5 px-2 text-justify">
                        <input type="hidden" value="{{ $datapembelian->idpembelian }}" name="idpembelian">
                        <div class="form-group">
                            <label>Nama Rekening</label>
                            <input type="text" name="nama" class="form-control" value="{{ session('pengguna')->nama }}"
                                required>

                        </div>
                        <div class="form-group">
                            <label>Tanggal Transfer</label>
                            <input type="date" name="tanggaltransfer" class="form-control" value="<?= date('Y-m-d') ?>"
                                required>

                        </div>
                        <div class="form-group">
                            <label>Foto Bukti</label>
                            <input type="file" name="bukti" class="form-control" required>
                        </div>
                    </div>
                    <div class="card py-2 px-2 text-justify">
                        <h3 style="color: black; font-weight:bold;">Jumlah Pesanan</h3>

                        <?php $totalbelanja = 0; ?>
                        @foreach ($dataproduk as $dp)
                        @php
                        $totalharga = $dp->harga * $dp->jumlah;
                        @endphp
                        <div class="row">
                            <div class="col-md-8">
                                <p style="color: black;">{{ $dp->nama }} ({{ $dp->jumlah }} x) Rp
                                    {{ number_format($dp->harga) }},-</p>
                            </div>
                            <div class="col-md-4">
                                <p style="color: black;font-weight: bold;" class="text-right">Rp
                                    {{ number_format($totalharga) }},-</p>
                            </div>
                        </div>

                        <?php $totalbelanja += $totalharga; ?>
                        @endforeach
                        <hr>
                        <div class="row">
                            <div class="col-md-8">
                                <h5 style="color: black; font-weight:bold;">Total</h5>
                            </div>
                            <div class="col-md-4">
                                <p style="color: black; font-weight:bold;" class="text-right">Rp
                                    {{ number_format($totalbelanja) }} <br> <span
                                        style="color: red; font-weight:400;">NON REFUNDABLE</span></p>
                            </div>
                        </div>
                        <hr>
                        <p>Dengan melanjutkan ke tahapan selanjutnya, Anda telah membaca dan setuju dengan <a href="#" style="color: #55acce;">Syarat & Kententuannya</a>.
                        </p>

                        <button class="btn btn-lg text-white" style="background-color: #55acce"
                            name="kirim">Kirimkan</button>
        </form>
    </div>
    </div>
    <div class="col-md-4">
        <div class="card py-2 px-2">
            <p>No Transaksi: <br> <span style="color: #000; font-weight:bold;">{{ $datapembelian->notransaksi }}</span>
            </p>
        </div>
        <div class="card mt-3 py-2 px-2">
            <h3 style="color: black;">{{ $dp->nama }}</h3>
            Kota Asal Pengiriman : Kabupaten Jepara
            <img src="{{ asset('foto/' . $dp->foto) }}" height="250px" alt="">
            <p style="color: #000;">{{ $datapembelian->alamat }}, {{ $datapembelian->kec }},
                {{ $datapembelian->kota }}, {{ $datapembelian->provinsi }},{{ $datapembelian->kode_pos }}</p>
            <table class="">
                <tr>
                    <td width="150px"><strong>Nama Penerima</strong></td>
                    <td>: {{ $datapembelian->nama }}</td>
                </tr>
                <tr>
                    <td>Tanggal Pemesanan</td>
                    <td>: {{ tanggal(date('Y-m-d', strtotime($datapembelian->tanggalbeli))) }}</td>
                </tr>
                <tr>
                    <td>No Telepon</td>
                    <td>: {{ $datapembelian->telepon }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: {{ $datapembelian->statusbeli }}</td>
                </tr>
            </table>
        </div>
    </div>
    </div>
    </div>
</section>
@endsection