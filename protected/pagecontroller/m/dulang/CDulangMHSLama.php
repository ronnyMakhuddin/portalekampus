<?php
prado::using ('Application.MainPageM');
class CDulangMHSLama Extends MainPageM {		
	public function onLoad($param) {
		parent::onLoad($param);				
        $this->showSubMenuAkademikDulang=true;
        $this->showDulangMHSLama=true;                
        $this->createObj('Finance');
        $this->createObj('Akademik');
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageDulangMHSLama'])||$_SESSION['currentPageDulangMHSLama']['page_name']!='m.dulang.DulangMHSLama') {
				$_SESSION['currentPageDulangMHSLama']=array('page_name'=>'m.dulang.DulangMHSLama','page_num'=>0,'search'=>false,'tahun_masuk'=>$_SESSION['tahun_masuk'],'iddosen_wali'=>'none','DataMHS'=>array());												
			}
            $_SESSION['currentPageDulangMHSLama']['search']=false;
            
            $this->tbCmbPs->DataSource=$this->DMaster->removeIdFromArray($_SESSION['daftar_jurusan'],'none');
            $this->tbCmbPs->Text=$_SESSION['kjur'];			
            $this->tbCmbPs->dataBind();	

            $tahun_masuk=$this->getAngkatan ();			            
            $this->tbCmbTahunMasuk->DataSource=$tahun_masuk	;					
            $this->tbCmbTahunMasuk->Text=$_SESSION['currentPageDulangMHSLama']['tahun_masuk'];						
            $this->tbCmbTahunMasuk->dataBind();

            $this->tbCmbTA->DataSource=$this->DMaster->removeIdFromArray($this->DMaster->getListTA($this->Pengguna->getDataUser('tahun_masuk')),'none');
            $this->tbCmbTA->Text=$_SESSION['ta'];
            $this->tbCmbTA->dataBind();			

            $semester=$this->DMaster->removeIdFromArray($this->setup->getSemester(),'none');  				
            $this->tbCmbSemester->DataSource=$semester;
            $this->tbCmbSemester->Text=$_SESSION['semester'];
            $this->tbCmbSemester->dataBind();

            $this->tbCmbOutputReport->DataSource=$this->setup->getOutputFileType();
            $this->tbCmbOutputReport->Text= $_SESSION['outputreport'];
            $this->tbCmbOutputReport->DataBind();

            $this->tbCmbOutputCompress->DataSource=$this->setup->getOutputCompressType();
            $this->tbCmbOutputCompress->Text= $_SESSION['outputcompress'];
            $this->tbCmbOutputCompress->DataBind();

            $this->populateData();
            $this->setInfoToolbar();
		}	
	}
    public function getDataMHS($idx) {		        
        return $this->Demik->getDataMHS($idx);
    }
    public function setInfoToolbar() {        
        $kjur=$_SESSION['kjur'];        
		$ps=$_SESSION['daftar_jurusan'][$kjur];
        $ta=$this->DMaster->getNamaTA($_SESSION['ta']);		
        $semester = $this->setup->getSemester($_SESSION['semester']);
		$tahunmasuk=$_SESSION['currentPageDulangMHSLama']['tahun_masuk'] == 'none'?'':'Tahun Masuk '.$this->DMaster->getNamaTA($_SESSION['currentPageDulangMHSLama']['tahun_masuk']);		        
		$this->lblModulHeader->Text="Program Studi $ps T.A $ta Semester $semester $tahunmasuk";        
	}
    public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDulangMHSLama']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageDulangMHSLama']['search']);
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}
	public function changeTbTA ($sender,$param) {				
		$_SESSION['ta']=$this->tbCmbTA->Text;		        
		$_SESSION['currentPageDulangMHSLama']['tahun_masuk']=$_SESSION['ta'];
		$this->tbCmbTahunMasuk->DataSource=$this->getAngkatan();
		$this->tbCmbTahunMasuk->Text=$_SESSION['currentPageDulangMHSLama']['tahun_masuk'];
		$this->tbCmbTahunMasuk->dataBind();		
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbTahunMasuk($sender,$param) {				
		$_SESSION['currentPageDulangMHSLama']['tahun_masuk']=$this->tbCmbTahunMasuk->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}
	public function changeTbPs ($sender,$param) {		
		$_SESSION['kjur']=$this->tbCmbPs->Text;
        $this->setInfoToolbar();
		$this->populateData();
	}	
	public function changeTbSemester ($sender,$param) {		
		$_SESSION['semester']=$this->tbCmbSemester->Text;        
        $this->setInfoToolbar();
		$this->populateData();
	}
    public function populateData($search=false) {
        $ta=$_SESSION['ta'];
		$idsmt=$_SESSION['semester'];
		$kjur=$_SESSION['kjur'];
		$tahun_masuk=$_SESSION['currentPageDulangMHSLama']['tahun_masuk'];
        $iddosen_wali=$_SESSION['currentPageDulangMHSLama']['iddosen_wali'];
        $str_dw = $iddosen_wali=='none'?'':" AND vdm.iddosen_wali=$iddosen_wali";
        $str_tahun_masuk=$tahun_masuk=='none'?'':" AND vdm.tahun_masuk=$tahun_masuk";      
        if ($search) {
            $str = "SELECT k.idkrs,k.tgl_krs,vdm.no_formulir,k.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.kjur,vdm.tahun_masuk,vdm.semester_masuk,vdm.idkelas,k.sah,k.tgl_disahkan,0 AS boolpembayaran FROM krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt'";
            $txtsearch=$this->txtKriteria->Text;
            switch ($this->cmbKriteria->Text) {                
                case 'nim' :
                    $clausa="AND vdm.nim='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nirm' :
                    $clausa="AND vdm.nirm='$txtsearch'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
                case 'nama' :
                    $clausa="AND vdm.nama_mhs LIKE '%$txtsearch%'";
                    $jumlah_baris=$this->DB->getCountRowsOfTable ("krs k,v_datamhs vdm WHERE k.nim=vdm.nim AND tahun='$ta' AND idsmt='$idsmt' $clausa",'vdm.nim');
                    $str = "$str $clausa";
                break;
            }
        }else{                            
            $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.iddosen_wali,d.tanggal FROM v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.tahun=$ta AND d.idsmt=$idsmt AND vdm.kjur='$kjur' $str_dw $str_tahun_masuk";
            $jumlah_baris=$this->DB->getCountRowsOfTable ("v_datamhs vdm,dulang d WHERE vdm.nim=d.nim AND d.tahun=$ta AND d.idsmt=$idsmt AND vdm.kjur='$kjur' $str_dw $str_tahun_masuk",'vdm.nim');
        }
		
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDulangMHSLama']['page_num'];
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$offset=$this->RepeaterS->CurrentPageIndex*$this->RepeaterS->PageSize;
		$limit=$this->RepeaterS->PageSize;
		if ($offset+$limit>$this->RepeaterS->VirtualItemCount) {
			$limit=$this->RepeaterS->VirtualItemCount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDulangMHSLama']['page_num']=0;}
		$str = "$str ORDER BY vdm.nama_mhs ASC LIMIT $offset,$limit";				        
		$this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','iddosen_wali','tanggal'));
		$result=$this->DB->getRecord($str);
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();
                
        $this->paginationInfo->Text=$this->getInfoPaging($this->RepeaterS);
	}
	public function cekNIM ($sender,$param) {		
        $nim=addslashes($param->Value);		
        if ($nim != '') {
            try {
                if (!isset($_SESSION['currentPageDulangMHSLama']['DataMHS']['no_formulir'])) {
                    
                    $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,vdm.semester_masuk,iddosen_wali,vdm.k_status,sm.n_status AS status,vdm.idkelas,ke.nkelas FROM v_datamhs vdm LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) LEFT JOIN status_mhs sm ON (vdm.k_status=sm.k_status) LEFT JOIN kelas ke ON (vdm.idkelas=ke.idkelas) WHERE vdm.nim='$nim'";
                    $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','k_status','status','idkelas','nkelas'));
                    $r=$this->DB->getRecord($str);	           
                    if (!isset($r[1])) {
                        throw new Exception ("Mahasiswa Dengan NIM ($nim) tidak terdaftar di Portal.");
                    }
                    $datamhs=$r[1];      
                    $this->Finance->setDataMHS($datamhs);                              
                    $datadulang=$this->Finance->getDataDulang($_SESSION['semester'],$_SESSION['ta']);
                    
                    if (isset($datadulang['iddulang'])) {
                        throw new Exception ("Mahasiswa Dengan NIM ($nim) telah daftar ulang di T.A dan Semester ini.");
                    }
                    $data=$this->Finance->getTresholdPembayaran($_SESSION['ta'],$_SESSION['semester'],true);						                                
                    if (!$data['bool']) {
                        throw new Exception ("Mahasiswa a.n ".$this->Finance->getDataMHS('nama_mhs')."($nim) tidak bisa daftar ulang karena baru membayar(".$this->Finance->toRupiah($data['total_bayar'])."), harus minimal setengahnya sebesar (".$this->Finance->toRupiah($data['ambang_pembayaran']).") dari total (".$this->Finance->toRupiah($data['total_biaya']).")");
                    }
                    $datamhs['idsmt']=$_SESSION['semester'];
                    $datamhs['ta']=$_SESSION['ta'];
                    $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                    $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
                    $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
                    $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
                    $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
                    $_SESSION['currentPageDulangMHSLama']['DataMHS']=$datamhs;
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function Go($param,$sender) {	
        if ($this->Page->isValid) {            
            $nim=addslashes($this->txtNIM->Text);
            $this->redirect('dulang.DetailDulangMHSLama',true,array('id'=>$nim));
        }
	}
    public function viewRecord($sender,$param) {	
		$this->idProcess='view';		
		$nim=$this->getDataKeyField($sender,$this->RepeaterS);	
        $str = "SELECT vdm.no_formulir,vdm.nim,vdm.nirm,vdm.nama_mhs,vdm.jk,vdm.tempat_lahir,vdm.tanggal_lahir,vdm.kjur,vdm.nama_ps,vdm.idkonsentrasi,k.nama_konsentrasi,vdm.tahun_masuk,semester_masuk,iddosen_wali,d.idkelas,d.k_status FROM v_datamhs vdm JOIN dulang d ON (d.nim=vdm.nim) LEFT JOIN konsentrasi k ON (vdm.idkonsentrasi=k.idkonsentrasi) WHERE vdm.nim='$nim'";
        $this->DB->setFieldTable(array('no_formulir','nim','nirm','nama_mhs','jk','tempat_lahir','tanggal_lahir','kjur','nama_ps','idkonsentrasi','nama_konsentrasi','tahun_masuk','semester_masuk','iddosen_wali','idkelas','k_status'));
        $r=$this->DB->getRecord($str);	           
        $datamhs=$r[1];
        $datamhs['nama_dosen']=$this->DMaster->getNamaDosenWaliByID ($datamhs['iddosen_wali']);
        $datamhs['nkelas']=$this->DMaster->getNamaKelasByID($datamhs['idkelas']);
        $datamhs['nama_konsentrasi']=($datamhs['idkonsentrasi']==0) ? '-':$datamhs['nama_konsentrasi'];                    
        $datamhs['status']=$this->DMaster->getNamaStatusMHSByID($datamhs['k_status']);
        $this->hiddensemestermasuk->Value=$datamhs['semester_masuk'];
        $this->hiddentahunmasuk->Value=$datamhs['tahun_masuk'];
        $this->Demik->setDataMHS($datamhs);
	}
    public function deleteRecord ($sender,$param) {			
		$nim=$sender->CommandParameter;		
		$idsmt=$this->hiddensemestermasuk->Value;
		$ta=$this->hiddentahunmasuk->Value;
		$this->DB->query ('BEGIN');
        $this->Demik->setDataMHS(array('nim'=>$nim));
        $datadulang=$this->Demik->getDataDulang($idsmt,$ta);
        $k_status=$datadulang['status_sebelumnya'];
        $str = "UPDATE register_mahasiswa SET k_status='$k_status' WHERE nim='$nim'";
		if ($this->DB->updateRecord($str)) {
            $this->DB->deleteRecord("dulang WHERE nim='$nim' AND tahun='$ta' AND idsmt='$idsmt'");
			$this->DB->deleteRecord("krs WHERE nim='$nim' AND tahun='$ta' AND idsmt='$idsmt'");		
			$this->DB->query ('COMMIT');
            $this->redirect('dulang.DulangMHSLama',true);
		}else {
			$this->DB->query ('ROLLBACK');
		}		
	}
}
?>