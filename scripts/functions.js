const messageQueue = []; // Fila de mensagens
let isMessageVisible = false; // Indica se uma mensagem está sendo exibida
export const rootPath = 'http://localhost/restaurante';

//Adiciona uma mensagem na fila de mensagens a serem mostradas (ver linha 15 e 16)
export function mensagem(message, isSuccess) {
    // Adiciona a mensagem à fila
    messageQueue.push({ message, isSuccess });

    // Se não houver nenhuma mensagem sendo exibida, processa a fila
    if (!isMessageVisible) {
        processMessageQueue();
    }
}

//Processa mensagens da fila de mensagens
export function processMessageQueue() {
    if (messageQueue.length === 0) {
        isMessageVisible = false; // Não há mais mensagens na fila
        return;
    }

    isMessageVisible = true; // Uma mensagem está sendo exibida
    const { message, isSuccess } = messageQueue.shift(); // Retira a próxima mensagem da fila

    // Cria o container
    const container = document.createElement('div');
    container.className = 'message-container'; // Usa uma classe para estilos
    container.textContent = message;

    // Define a cor do fundo com base no sucesso ou erro
    container.style.backgroundColor = isSuccess ? '#4caf50' : '#f44336'; // Verde para sucesso, vermelho para erro
    container.style.opacity = '1'; // Inicia o fade-in

    // Adiciona o container ao body
    document.body.appendChild(container);
    

    setTimeout(() => {
        container.style.opacity = '0'; // Inicia o fade-out
        setTimeout(() => {
            document.body.removeChild(container);
            processMessageQueue(); // Processa a próxima mensagem na fila
        }, 1000); // Aguarda o término do fade-out
    }, 1000); // Mantém a mensagem visível por 1 segundo
    
}

//Footer
export async function mostrarRodape() {
    let footer = document.createElement('footer');
    try {
        const footer_response = await fetch('https://guismith.github.io/portfolio/footer.html');
        const footer_html = await footer_response.text();
        footer.innerHTML = footer_html;
        document.body.appendChild(footer);
        console.log("Rodapé adicionado");
    } catch (error) {
        footer.innerHTML = "<p>Não foi possível inserir o Rodapé do site, clique <a target='_blank' href = 'https://github.com/GuiSmith'>aqui</a> para ter acesso aos dados do desenvolvedor </p>";
        console.log("Não foi possível inserir o Rodapé do site, veja o motivo abaixo");
        console.error(error);
    }
}

export async function mostrarNavbar(){
    try {
        const html_response = await fetch(`${rootPath}/componentes/nav.html`);
        const html_nav = await html_response.text();
        const navbar = document.createElement('nav');
        navbar.innerHTML = html_nav;
        document.body.insertBefore(navbar,document.body.firstChild);
    } catch (error) {
        console.log(`Não foi possível mostrar a barra de navegação, veja o erro a seguir`);
        console.error(error);
        return;
    }
}

export async function mostrarHead(){
    try {
        const html_response = await fetch(`${rootPath}/componentes/head.html`);
        const html_head = await html_response.text();
        document.head.innerHTML = html_head;
        //console.log(head);
    } catch (error) {
        console.log(`Não foi possível mostrar o elemento head da página, veja o erro a seguir`);
        console.error(error);
        return;
    }
}

export async function buscarUsuario(token){
    let usuario_response = await fetch(`${rootPath}/back/api/usuario.php?token=${token}`);
    return await usuario_response.json();
}