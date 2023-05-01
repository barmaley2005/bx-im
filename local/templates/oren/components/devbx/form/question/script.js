const styleSelect = (selector) => {
    const selectBlockAll = document.querySelectorAll(selector);

    selectBlockAll.forEach(selectBlock => {
        const
            popular = document.getElementById('popular'),
            header = selectBlock.querySelector('.select-head'),
            input = header.querySelector('.select-head__input'),
            container = selectBlock.querySelector('.select-container'),
            list = selectBlock.querySelector('.select-list'),
            liAll = list.querySelectorAll('li');

        header.addEventListener('click', () => {
            if (!header.classList.contains('_show')) {

                header.classList.add('_show');
                header.classList.add('_label');
                container.style.cssText = `
            height: ${list.clientHeight + 36}px
          `;
                selectBlock.style.cssText = `
            z-index: 5
          `
            } else {
                header.classList.remove('_show');
                if (!header.classList.contains('_label2')) {
                    header.classList.remove('_label');
                }
                container.removeAttribute('style');
                setTimeout(() => {
                    selectBlock.removeAttribute('style')
                }, 400);
            }
        })

        liAll.forEach(liElem => {
            liElem.addEventListener('click', () => {

                if (popular) {
                    popular.innerHTML = liElem.innerHTML.trim();
                    header.classList.remove('_show');
                    container.removeAttribute('style');
                    console.log(23);
                } else {
                    input.setAttribute('value', `${liElem.textContent.trim()}`);
                    header.classList.remove('_show');
                    container.removeAttribute('style')
                    header.classList.add('_label2');;
                    console.log(12);
                }
            })
        })

        document.body.addEventListener('click', (e) => {
            let target = e.target;

            if (!target.closest('.select')) {
                header.classList.remove('_show');
                container.removeAttribute('style');
                console.log(45);
                if (!header.classList.contains('_label2')) {
                    header.classList.remove('_label');
                }
            }
        })
    })
}
