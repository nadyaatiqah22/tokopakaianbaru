<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // Jumlah pesanan
        $jumlahPesanan = DB::table('pembelian')->count();

        // Jumlah user
        $jumlahUser = DB::table('pengguna')->count();

        // Jumlah stok
        $jumlahStok = DB::table('produk')->sum('stok');

        // Data untuk grafik pemesanan
        $orderData = DB::table('pembelian')
            ->select(DB::raw('DATE(tanggalbeli) as date'), DB::raw('count(*) as jumlah'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Data untuk grafik stok
        $stockData = DB::table('produk')
            ->select(DB::raw('DATE(tanggal) as date'), DB::raw('sum(stok) as jumlah'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Menggabungkan data pemesanan dan stok berdasarkan tanggal
        $dates = $orderData->pluck('date')->merge($stockData->pluck('date'))->unique()->sort()->values();

        $orderDataByDate = $orderData->keyBy('date');
        $stockDataByDate = $stockData->keyBy('date');

        $combinedOrderData = $dates->map(function ($date) use ($orderDataByDate) {
            return $orderDataByDate->has($date) ? $orderDataByDate[$date]->jumlah : 0;
        });

        $combinedStockData = $dates->map(function ($date) use ($stockDataByDate) {
            return $stockDataByDate->has($date) ? $stockDataByDate[$date]->jumlah : 0;
        });

        return view('admin.dashboard', [
            'jumlahPesanan' => $jumlahPesanan,
            'jumlahUser' => $jumlahUser,
            'jumlahStok' => $jumlahStok,
            'combinedLabels' => $dates,
            'combinedOrderData' => $combinedOrderData,
            'combinedStockData' => $combinedStockData,
        ]);
    }

    public function kategori()
    {
        $data['kategori'] = DB::table('kategori')->get();
        return view('admin.kategori', $data);
    }

    public function tambahkategori()
    {

        return view('admin.tambahkategori');
    }

    public function simpankategori(Request $request)
    {
        $data = [
            'namakategori' => $request->kategori,
            'idkategori' => $request->kategori,
        ];
        KategoriModel::create($data);
        session()->flash('alert', 'Berhasil menambahkan data!');
        return redirect('admin/kategori');
    }

    public function ubahkategori($id)
    {
        $data['kategori'] = KategoriModel::where('idkategori', $id)->first();
        return view('admin.ubahkategori', $data);
    }

    public function updatekategori(Request $request, $id)
    {
        $data = [
            'namakategori' => $request->kategori
        ];
        KategoriModel::where('idkategori', $id)->update($data);
        session()->flash('alert', 'Berhasil mengubah data!');
        return redirect('admin/kategori');
    }

    public function hapuskategori($id)
    {
        KategoriModel::where('idkategori', $id)->delete();
        session()->flash('alert', 'Berhasil menghapus data!');
        return redirect('admin/kategori');
    }

    public function produk()
    {
        $produk = DB::table('produk')->leftJoin('kategori', 'produk.idkategori', '=', 'kategori.idkategori')->orderBy('idproduk', 'DESC')->get();
        $data['produk'] = $produk;
        return view('admin.produk', $data);
    }

    public function tambahproduk()
    {
        $data['kategori'] = DB::table('kategori')->get();

        return view('admin.tambahproduk', $data);
    }

    public function simpanproduk(Request $request)
    {
        $namafoto = $request->file('foto')->getClientOriginalName();
        $request->file('foto')->move(public_path('foto'), $namafoto);

        DB::table('produk')->insert([
            'nama' => $request->input('nama'),
            'idkategori' => $request->input('idkategori'),
            'harga' => $request->input('harga'),
            'stok' => $request->input('stok'),
            'foto' => $namafoto,
            'deskripsi' => $request->input('deskripsi'),
        ]);
        session()->flash('alert', 'Berhasil menambah data!');

        return redirect('admin/produk');
    }

    public function ubahproduk($id)
    {
        $data['produk'] = DB::table('produk')->where('idproduk', $id)->first();
        $data['kategori'] = DB::table('kategori')->get();
        return view('admin.ubahproduk', $data);
    }

    public function updateproduk(Request $request, $id)
    {
        $data = [
            'nama' => $request->input('nama'),
            'idkategori' => $request->input('idkategori'),
            'harga' => $request->input('harga'),
            'stok' => $request->input('stok'),
            'deskripsi' => $request->input('deskripsi'),
        ];
        $produk = DB::table('produk')->where('idproduk', $id)->first();
        $fotoPath = public_path('foto/' . $produk->foto);
        if ($request->hasFile('foto')) {
            $namafoto = $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move(public_path('foto'), $namafoto);
            $data['foto'] = $namafoto;
        }
        DB::table('produk')->where('idproduk', $id)->update($data);
        session()->flash('alert', 'Berhasil mengubah data!');
        return redirect('admin/produk');
    }

    public function hapusproduk($id)
    {
        DB::table('produk')->where('idproduk', $id)->delete();
        session()->flash('alert', 'Berhasil menghapus data!');
        return redirect('admin/produk');
    }

    public function pengguna()
    {
        $pengguna = DB::table('pengguna')->where('level', 'Pelanggan')->get();

        $data = [
            'pengguna' => $pengguna,
        ];

        return view('admin.pengguna', $data);
    }

    public function hapuspengguna($id)
    {
        DB::table('pengguna')->where('id', $id)->delete();

        return redirect('admin/pengguna')->with('success', 'Pengguna berhasil dihapus');
    }

    public function logout()
    {
        session()->flush();
        return redirect('home')->with('alert', 'Anda Telah Logout');
    }

    public function pembelian()
    {
        $pembelian = DB::table('pembelian')->orderBy('pembelian.tanggalbeli', 'desc')->orderBy('pembelian.idpembelian', 'desc')->get();

        $dataproduk = [];
        foreach ($pembelian as $row) {
            $idpembelian = $row->idpembelian;
            $produk = DB::table('pembelianproduk')
                ->join('produk', 'pembelianproduk.idproduk', '=', 'produk.idproduk')
                ->where('idpembelian', $idpembelian)
                ->get();
            $dataproduk[$idpembelian] = $produk;
        }


        $data = [
            'pembelian' => $pembelian,
            'dataproduk' => $dataproduk,
        ];
        return view('admin.pembelian', $data);
    }

    public function pembayaran($id)
    {
        $datapembelian = DB::table('pembelian')->where('pembelian.idpembelian', $id)->first();
        $dataproduk = DB::table('pembelianproduk')
            ->join('produk', 'pembelianproduk.idproduk', '=', 'produk.idproduk')
            ->where('idpembelian', $id)
            ->get();
        $pembelianFoto = DB::table('pembelian_foto')->where('id_pembelian', $datapembelian->idpembelian)->get();
        $pembayaran = DB::table('pembayaran')->where('idpembelian', $id)->first();

        $data = [
            'datapembelian' => $datapembelian,
            'dataproduk' => $dataproduk,
            'pembayaran' => $pembayaran,
            'pembelianFoto' => $pembelianFoto,
        ];
        return view('admin.pembayaran', $data);
    }

    public function exportpdf()
    {
        // Mengambil data pembelian dan produk
        $pembelian = DB::table('pembelian')->join('pengguna', 'pengguna.id', '=', 'pembelian.id')
            ->orderBy('pembelian.tanggalbeli', 'desc')->orderBy('pembelian.idpembelian', 'desc')->get();

        $dataproduk = [];
        foreach ($pembelian as $row) {
            $idpembelian = $row->idpembelian;
            $produk = DB::table('pembelianproduk')
                ->join('produk', 'pembelianproduk.idproduk', '=', 'produk.idproduk')
                ->where('idpembelian', $idpembelian)
                ->get();
            $dataproduk[$idpembelian] = $produk;
        }

        $data = [
            'pembelian' => $pembelian,
            'dataproduk' => $dataproduk,
        ];

        // Load view untuk laporan PDF
        $view = view('admin.pembelian_pdf', $data)->render();

        // Inisialisasi DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Muat konten HTML
        $dompdf->loadHtml($view);

        // Set ukuran kertas dan orientasi (potrait atau landscape)
        $dompdf->setPaper('A4', 'landscape');

        // Render PDF
        $dompdf->render();

        // Output PDF
        $dompdf->stream('laporan_pembelian.pdf', ['Attachment' => 1]);
    }

    public function simpanpembayaran($id, Request $request)
    {
        if ($request->has('proses')) {
            $statusbeli = $request->input('statusbeli');
            $pembelianproduk = DB::table('pembelianproduk')->where('idpembelian', $id)->get();

            if ($request->hasFile('foto')) {
                $namafoto = date('Ymdhis') . '-' . $request->file('foto')->getClientOriginalName();
                $request->file('foto')->move(public_path('foto'), $namafoto);

                // Insert the photo into the new table
                DB::table('pembelian_foto')->insert([
                    'id_pembelian' => $id,
                    'status' => $statusbeli,
                    'foto' => $namafoto,
                ]);
            }

            // Update status pembelian
            DB::table('pembelian')->where('idpembelian', $id)->update([
                'statusbeli' => $statusbeli,
                'catatan' => $request->input('catatan'),
            ]);

            if ($request->statusbeli == 'Pesanan Di Terima') {
                foreach ($pembelianproduk as $key => $value) {
                    $idproduk = $value->idproduk;
                    $jumlahbeli = $value->jumlah;

                    // Mengurangi stok produk
                    $produk = DB::table('produk')->where('idproduk', $idproduk)->first();
                    $stokbaru = $produk->stok - $jumlahbeli;

                    DB::table('produk')->where('idproduk', $idproduk)->update(['stok' => $stokbaru]);
                }
            }

            return redirect('admin/pembelian');
        }
    }

    public function laporan()
    {
        return view('admin.laporan');
    }

    public function laporancetak(Request $request)
    {
        // Retrieve the date range from the request
        $tanggalawal = $request->input('tanggalawal');
        $tanggalakhir = $request->input('tanggalakhir');

        // Fetch purchases with the status 'Pesanan Di Terima' and within the date range
        $pembelian = DB::table('pembelian')
            ->where('statusbeli', 'Pesanan Di Terima')
            ->whereBetween('tanggalbeli', [$tanggalawal, $tanggalakhir])
            ->orderBy('tanggalbeli', 'desc')
            ->orderBy('idpembelian', 'desc')
            ->get();

        // Fetch related products for each purchase
        $dataproduk = [];
        foreach ($pembelian as $row) {
            $idpembelian = $row->idpembelian;
            $produk = DB::table('pembelianproduk')
                ->join('produk', 'pembelianproduk.idproduk', '=', 'produk.idproduk')
                ->where('idpembelian', $idpembelian)
                ->get();
            $dataproduk[$idpembelian] = $produk;
        }

        // Calculate total purchases
        $totalPembelian = $pembelian->sum('totalbeli');

        // Prepare data for the view
        $data = [
            'pembelian' => $pembelian,
            'dataproduk' => $dataproduk,
            'tanggalawal' => $tanggalawal,
            'tanggalakhir' => $tanggalakhir,
            'totalPembelian' => $totalPembelian,  // Pass total purchases to the view
        ];

        return view('admin.laporancetak', $data);
    }
}
