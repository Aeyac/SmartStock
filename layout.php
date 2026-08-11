<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <title>Suppliers</title>
</head>
<body class="bg-slate-50">
<!--Includes-->
<?php include "../Includes/Header.php"; ?>
<?php include "../Includes/Sidebar.php"; ?>

  <div class="w-full max-w-[1500px] mx-auto">
    <!--Main Content-->
    <main class="mt-14 mb-16 lg:mb-0 lg:ml-[248px] py-5 px-4 md:px-8 overflow-x-hidden">

      <!--Page Header-->
      <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <!--Page Title-->
        <div class="hidden md:flex flex-col gap-1">
          <h1 class="text-3xl font-semibold leading-tight text-slate-950">Suppliers</h1>
          <p class="text-sm text-slate-900 font-normal">Manage your suppliers, contact information, and business relationships.</p>
        </div>

        <!--Header Actions-->
        <div class="flex items-center flex-row gap-3 md:gap-6 mt-0 md:mt-3">

        <!--Stats-->
        <div class="grid grid-cols-2 gap-3 flex-1 lg:flex lg:flex-none lg:w-96 lg:h-24 lg:gap-0 lg:items-center lg:border lg:border-slate-200 lg:rounded-md lg:bg-white lg:shadow-sm lg:py-6">
          <!--Total Partners-->
          <div class="flex-1 bg-white border border-slate-200 rounded-md p-4 shadow-sm lg:bg-transparent lg:border-0 lg:rounded-none lg:shadow-none lg:p-0 lg:px-6 lg:relative lg:after:content-[''] lg:after:absolute lg:after:top-1/2 lg:after:right-0 lg:after:-translate-y-1/2 lg:after:w-px lg:after:h-[30px] lg:after:bg-slate-200">
            <span class="text-xs font-normal uppercase text-blue-600 lg:text-slate-800">
              Total Partners
            </span>

            <h2 class="flex items-center gap-2 mt-1 text-xl lg:text-2xl font-semibold text-slate-950">
              124
              <i class="ti ti-users text-blue-500 text-base"></i>
            </h2>
          </div>

          <!--Active Now-->
          <div class="flex-1 bg-white border border-slate-200 rounded-md p-4 shadow-sm lg:bg-transparent lg:border-0 lg:rounded-none lg:shadow-none lg:p-0 lg:px-6">
            <span class="text-xs font-normal uppercase text-blue-600 lg:text-slate-800">
              Active Now
            </span>

            <h2 class="flex items-center gap-2 mt-1 text-xl lg:text-2xl font-semibold text-slate-950">
              118
              <span class="w-2 h-2 rounded-full bg-green-500"></span>
            </h2>
          </div>
        </div>

          <!--Add Button-->
          <button id="openModal" type="button" 
            class="hidden lg:flex items-center justify-center bg-slate-900 border-none text-white text-sm shrink-0 px-5 py-3 rounded-lg gap-2 cursor-pointer shadow-sm transition duration-300 hover:bg-slate-600 focus:outline-none active:scale-[0.98]">
            <i class="ti ti-plus text-xl transition-colors duration-300"></i>
            Add Supplier
          </button>
        </div>
      </section>

      <!--Tool Wrapp-->
      <section class="mt-8 border-0 rounded-none shadow-none lg:border lg:border-slate-200 lg:rounded-lg lg:overflow-hidden lg:shadow-sm">
        <!--Tool Bar-->
        <div class="flex flex-col md:bg-white lg:rounded-t-lg lg:rounded-b-none md:rounded-lg gap-3 p-0 md:p-5 sm:flex-row sm:items-center sm:justify-between">

          <!--Search Bar-->
          <div class="relative w-full md:w-80">
            <i class="ti ti-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-800 text-lg cursor-pointer"></i>
            <input class="w-full h-11 bg-slate-200 md:h-10 pr-4 pl-10 md:bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm hover:border-slate-900 focus:border-slate-900 focus:bg-slate-100"
             type="text" placeholder="Search by name or contact...">
          </div>

          <!--Right section-->
          <div class="flex items-center justify-between gap-3">

            <!--Filter Button-->
            <div class="flex items-center text-xs gap-1 p-0 md:p-1 md:bg-slate-100 md:rounded-lg md:border md:border-slate-200">              
              <button data-filter="all"
                class="toolbar-btn md:flex-none text-slate-700 rounded-2xl md:rounded-lg px-3 md:px-4 py-1.5 cursor-pointer md:transition md:duration-300 md:hover:bg-slate-300 md:focus:outline-none">
                All
              </button>

              <button data-filter="active"
                class="toolbar-btn md:flex-none text-slate-700 rounded-2xl md:rounded-lg px-2 md:px-4 py-1.5 cursor-pointer md:transition md:duration-300 md:hover:bg-slate-300 md:focus:outline-none">
                Active
              </button>

              <button data-filter="inactive"
                class="toolbar-btn md:flex-none text-slate-700 rounded-2xl md:rounded-lg px-2 md:px-4 py-1.5 cursor-pointer md:transition md:duration-300 md:hover:bg-slate-300 md:focus:outline-none">
                Inactive
              </button>
            </div>

            <div class="flex items-center gap-2">
              <!-- Sort Button -->
              <button id="sortBtn"
                  class="flex items-center justify-center text-xl text-slate-600 bg-slate-200 rounded-2xl md:rounded-lg px-2 py-1.5 md:hover:text-slate-800 md:transtion md:duration-300 cursor-pointer">
                <i class="ti ti-sort-ascending-2"></i>
              </button>

              <!--Download Button, itinatago sa mobile-->
              <button id="downloadBtn"
                  class="text-xl text-slate-600 md:hover:text-slate-800 md:transtion md:duration-300 cursor-pointer">
                <i class="ti ti-download"></i>
              </button>
            </div>
          </div>
        </div>

        <!--Desktop Table, mobile & tablet-->
        <section class="hidden lg:block bg-white">
          <table class="w-full border-collapse">
            <thead>
              <tr>
                <th class="text-left p-3 bg-slate-100 border-t border-slate-200 text-xs uppercase font-normal">Supplier Name</th>
                <th class="text-left p-3 bg-slate-100 border-t border-slate-200 text-xs uppercase font-normal">Contact Number</th>
                <th class="text-left p-3 bg-slate-100 border-t border-slate-200 text-xs uppercase font-normal">Email Address</th>
                <th class="text-left p-3 bg-slate-100 border-t border-slate-200 text-xs uppercase font-normal">Status</th>
              </tr>
            </thead>

            <tbody id="supplierTable">
            <!--Supplier.json-->
            </tbody>
          </table>
        </section>

        <!--Mobile & Tablet table List-->
        <div id="supplierListMobile" class="lg:hidden flex flex-col gap-3 mt-4">
          <!--Supplier.json-->
        </div>

      </section>

      <!--Pagination-->
      <div class="mt-2 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white border border-slate-200 rounded-lg px-4 py-3">
        <p class="text-sm text-slate-500">
          Showing <span id="paginationStart" class="font-medium text-slate-900">0</span>
          to <span id="paginationEnd" class="font-medium text-slate-900">0</span>
          of <span id="paginationTotal" class="font-medium text-slate-900">0</span> results
        </p>

        <div class="flex items-center gap-1">
          <button id="prevPageBtn"
            class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 transition duration-300 cursor-pointer hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed">
            <i class="ti ti-chevron-left text-base"></i>
          </button>

          <div id="paginationNumbers" class="flex items-center gap-1"></div>

          <button id="nextPageBtn"
            class="w-8 h-8 flex items-center justify-center rounded-md border border-slate-200 text-slate-500 transition duration-300 cursor-pointer hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed">
            <i class="ti ti-chevron-right text-base"></i>
          </button>
        </div>
      </div>
    </main>

    <!--Floating Add Button-->
    <button id="openModalMobile"
      class="lg:hidden fixed bottom-10 right-4 w-14 h-14 bg-slate-900 text-white rounded-full shadow-lg flex items-center justify-center z-40">
      <i class="ti ti-plus text-2xl"></i>
    </button>

    <!-- Sort Modal -->
    <div id="sortModal"
      class="fixed inset-0 bg-black/40 hidden items-end md:items-center justify-center backdrop-blur-xs z-100">
      <div id="sortDialog"
        class="bg-white w-full md:w-96 rounded-t-3xl md:rounded-2xl p-5">
          <h2 class="relative text-center border-b border-slate-200 pb-4 text-md font-semibold mb-4 mt-1 after:content-[''] after:absolute after:-top-3 after:left-1/2 after:-translate-x-1/2 after:w-8 after:h-1 after:bg-slate-300 after:rounded-full">Sort By</h2>

        <div class="space-y-2">
          <button class="sort-option w-full text-left p-3 rounded-lg md:hover:bg-slate-100 md:cursor-pointer" data-sort="az">Name (A-Z)</button>
          <button class="sort-option w-full text-left p-3 rounded-lg md:hover:bg-slate-100 md:cursor-pointer" data-sort="za">Name (Z-A)</button>
          <button class="sort-option w-full text-left p-3 rounded-lg md:hover:bg-slate-100 md:cursor-pointer" data-sort="active">Active First</button>
          <button class="sort-option w-full text-left p-3 rounded-lg md:hover:bg-slate-100 md:cursor-pointer" data-sort="inactive">Inactive First</button>
        </div>

        <button id="cancelSort"
            class="mt-6 w-full bg-slate-900 text-white rounded-lg py-3 md:hover:bg-slate-700 md:transition-all md:duration-300 md:cursor-pointer">
            Cancel
        </button>
      </div>
    </div>

    <!-- Download Modal -->
    <div id="downloadModal"
      class="fixed inset-0 bg-black/40 hidden items-end md:items-center justify-center backdrop-blur-xs z-100">

      <div id="downloadDialog"
        class="bg-white w-full md:w-96 rounded-t-3xl md:rounded-2xl p-5">
          <h2 class="relative text-center border-b border-slate-200 pb-4 text-md font-semibold mb-4 mt-1 after:content-[''] after:absolute after:-top-3 after:left-1/2 after:-translate-x-1/2 after:w-8 after:h-1 after:bg-slate-300 after:rounded-full">Export Data</h2>

        <div class="space-y-2">
          <button class="export-option w-full text-left p-3 rounded-lg hover:bg-slate-100 md:cursor-pointer" data-export="pdf">Export as PDF</button>
          <button class="export-option w-full text-left p-3 rounded-lg hover:bg-slate-100 md:cursor-pointer" data-export="excel">Export as Excel</button>
          <button class="export-option w-full text-left p-3 rounded-lg hover:bg-slate-100 md:cursor-pointer" data-export="csv">Export as CSV</button>
        </div>

        <button id="cancelDownload"
          class="mt-6 w-full bg-slate-900 text-white rounded-lg py-3 md:hover:bg-slate-700 md:transition-all md:duration-300 md:cursor-pointer">
          Cancel
        </button>
      </div>
    </div>

    <!--Supplier Modal-->
    <div id="supplierModal"
      class="fixed inset-0 hidden items-center justify-center bg-black/40 backdrop-blur-xs z-[100] opacity-0 transition-opacity duration-200 ease-out">

      <div id="supplierDialog" 
        class="w-11/12 sm:w-[420px] overflow-hidden bg-white rounded-lg shadow-xl transform scale-95 opacity-0 transition-all duration-200 ease-out">
        
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
          <h2 class="text-lg md:text-xl font-semibold text-slate-800">New Supplier Entry</h2>

          <button id="closeModal" class="text-slate-400 hover:text-slate-900 cursor-pointer">
            <i class="ti ti-x text-xl"></i>
          </button>
        </div>

        <form class="rounded-b-lg bg-slate-50">
          <div class="py-5 px-6 flex flex-col gap-5">
            <div class="flex flex-col gap-1.5">
              <label class="font-medium uppercase text-xs text-slate-600">Supplier Name</label>
              <input type="text" placeholder="e.g. ABC Hardware Supplies" name="supplier_name"
                class="w-full h-12 md:h-8 px-3 border border-slate-200 rounded-md bg-white outline-none text-sm hover:border-slate-900 focus:border-slate-900">
              </div>

            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1 flex flex-col gap-1.5">
                <label class="text-xs font-medium uppercase text-slate-600">Contact Number</label>
                <input type="text" placeholder="e.g. 0917 123 4567" name="contact_number"
                  class="w-full h-12 md:h-8 px-3 border border-slate-200 rounded-md bg-white outline-none text-sm hover:border-slate-900 focus:border-slate-900">
              </div>
              <div class="flex-1 flex flex-col gap-1.5">
                <label class="text-xs font-medium uppercase text-slate-600">Category</label>
                <select name="category"
                  class="w-full h-12 md:h-8 px-3 border border-slate-200 rounded-md bg-white outline-none text-sm hover:border-slate-900 focus:border-slate-900">
                  <option>Hardware</option>
                  <option>Software</option>
                  <option>Logistic</option>
                  <option>Raw Material</option>
                </select>
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-medium uppercase text-xs text-slate-600">Email Address</label>
              <input type="email" placeholder="e.g. supplier@email.com" name="email"
                class="w-full h-12 md:h-8 px-3 border border-slate-200 rounded-md bg-white outline-none text-sm hover:border-slate-900 focus:border-slate-900">
            </div>
          </div>

          <div class="flex flex-row justify-end gap-3 px-6 py-5 bg-white rounded-b-lg border-t border-slate-200">
            <button type="button" name="cancel" id="cancelModal"
              class="text-sm font-normal h-11 text-slate-900 hover:text-slate-900 px-5 cursor-pointer bg-white border border-slate-200 rounded-lg transition-all duration-200 hover:bg-slate-100">
              Cancel
            </button>

            <button type="submit"
              class="bg-slate-900 border-none h-11 text-white text-sm px-5 rounded-lg cursor-pointer shadow-sm transition-all duration-200 hover:bg-slate-600 hover:text-white hover:shadow-sm">
              Save Supplier
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

<!--JavaScript-->
<!--JavaScript-->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="../Assets/JS/Suppliers.js"></script>
<script src="../Assets/JS/Layout.js"></script>
</body>
</html>