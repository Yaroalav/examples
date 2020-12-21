const dateNow = Date.now();

describe('Test for adding keyword', () => {
    beforeEach(() => {
        cy.login('login', 'password');

        cy
            .get('[data-cy=add-keyword-draft-counter]')
            .should('not.exist')
            .get('[data-cy=add-keyword-button]')
            .as('add-keyword')
            .should('have.class', 'button-primary')
            .click()
            .url().should('include', '/add');
    });

    it('Web tab. Test for fields validations', () => {
        cy.urlNegativeTests('web');

        cy
            .get('[data-cy=add-keyword-web-keyword-textarea]').as('keyword')
            .should('have.class', 'text-input_invalid');
        
        cy
            .get('[data-cy=bulk-label] .toggler')
            .click()
            .get('@keyword')
            .type('test').should('have.class', 'text-input_invalid').clear()
            .type('test.com:').should('have.class', 'text-input_invalid').clear()
            .type(':test').should('have.class', 'text-input_invalid').clear()
            .type(':').should('have.class', 'text-input_invalid').clear()
            .type('test.com:test:').should('have.class', 'text-input_invalid').clear()
            .type('::test').should('have.class', 'text-input_invalid').clear()
            .type('test.com::test').should('have.class', 'text-input_invalid').clear()
            .type(':test:test').should('have.class', 'text-input_invalid').clear()
            .type('test:test').should('have.class', 'text-input_invalid').clear()
            .type('test.:test').should('have.class', 'text-input_invalid').clear()
            .type('.com:test').should('have.class', 'text-input_invalid').clear()
            .type('test.com:test,').should('have.class', 'text-input_invalid').clear()
            .type('test.com:test,test1:').should('have.class', 'text-input_invalid').clear();
    });

    it('Web tab. Fill all fields and send request to add keyword', () => {
        cy
            .get('ul.dropdown-menu')
            .should('not.be.visible')
            .get('[data-cy=add-keyword-web-location]')
            .should('not.exist')
            .get('[data-cy=add-keyword-web-suggestions]')
            .should('not.exist')
            .get('[data-cy=add-keyword-web-tags] input')
            .as('tags-input')
            .should('not.be.visible');

        cy
            .get('[data-cy=add-keyword-web-url-input]')
            .as('url')
            .should('have.class', 'text-input')
            .type('test.com')
            .should('not.have.class', 'text-input_invalid');
        
        cy
            .get('[data-cy=exact-label] .checkbox')
            .as('exact')
            .should('have.not.class', 'checkbox_active')
            .click()
            .and('have.class', 'checkbox_active');
        
        cy
            .get('[data-cy=add-keyword-web-region]')
            .as('region').click()
            .get('ul.dropdown-menu input.form-control')
            .eq(0).focus().type('google.ca')
            .get('ul.dropdown-menu')
            .eq(0).contains('li', 'google.ca')
            .click()
            .get('@region')
            .should('have.class', 'custom-dropdown_filled')
            .contains('google.ca');
        
        cy
            .get('[data-cy=add-keyword-web-language]')
            .as('language').click()
            .get('ul.dropdown-menu input.form-control')
            .eq(1).focus().type('en-ca')
            .get('ul.dropdown-menu')
            .eq(1).contains('li', 'en-ca English (Canada)')
            .click()
            .get('@language')
            .should('have.class', 'custom-dropdown_filled')
            .contains('en-ca English (Canada)');
        
        cy
            .get('[data-cy=add-keyword-web-platform] .radio-button__content')
            .eq(1)
            .click();
        
        cy
            .get('[data-cy=add-keyword-web-location-label] .checkbox')
            .as('location-checkbox')
            .should('have.not.class', 'checkbox_active')
            .click()
            .and('have.class', 'checkbox_active')
            .get('[data-cy=add-keyword-web-location]')
            .as('location')
            .should('exist')
            .focus().type('Paris, France')
            .get('.pac-container')
            .eq(0).click()
            .get('@location')
            .should('have.class', 'text-input')
            .and('have.value', 'Paris, France');

        cy
            .get('[data-cy=add-keyword-web-listings-label] .checkbox')
            .as('listings-checkbox')
            .should('have.class', 'checkbox_active')
            .click()
            .and('have.not.class', 'checkbox_active')
            .get('[data-cy=add-keyword-web-listings]')
            .as('listings')
            .should('exist')
            .focus().type('Paris, France')
            .get('.pac-container')
            .eq(1).click()
            .get('@listings')
            .should('have.class', 'text-input')
            .and('have.value', 'Paris, France');

        cy.keywordTextareaPositiveTests('web', dateNow);

        cy
            .get('[data-cy=add-keyword-web-tags] .toggler')
            .click()
            .get('@tags-input')
            .should('be.visible')
            .type('test{enter}')
            .get('.ti-tag')
            .as('tag')
            .contains('test')
            .get('.ti-icon-close')
            .click()
            .get('@tag')
            .should('not.exist')
            .get('@tags-input')
            .type(`tag ${dateNow}{enter}`)
            .get('@tag')
            .contains(`tag ${dateNow}`);

        cy
            .get('[data-cy=add-keyword-cancel]')
            .click()
            .url()
            .should('include', '/groups/')
            .and('not.include', '/add')
            .get('[data-cy=add-keyword-draft-counter]')
            .contains('1')
            .get('@add-keyword')
            .click()
            .url()
            .should('include', '/add')
            .get('@url')
            .should('have.value', 'test.com')
            .get('@exact')
            .should('have.class', 'checkbox_active')
            .get('@region')
            .should('have.class', 'custom-dropdown_filled')
            .contains('google.ca')
            .get('@language')
            .should('have.class', 'custom-dropdown_filled')
            .contains('en-ca English (Canada)')
            .get('[data-cy=add-keyword-web-platform] .radio-button__round')
            .eq(1)
            .should('have.class', 'radio-button__round_active')
            .get('@location-checkbox')
            .should('have.class', 'checkbox_active')
            .get('@listings-checkbox')
            .should('have.not.class', 'checkbox_active')
            .get('@keywords')
            .should('have.not.class', 'text-input_invalid')
            .and('have.value', `keyword ${dateNow}`)
            .get('@tag')
            .contains(`tag ${dateNow}`);

        cy
            .get('[data-cy=add-keyword-web-save]')
            .click()
            .get('.toast')
            .should('have.class', 'toast-success')
            .url()
            .should('include', '/groups/');
    });

    it('Web tab. Bulk import', () => {
        cy
            .get('[data-cy=add-keyword-web-url-input]')
            .as('url')
            .should('not.be.disabled')
            .get('[data-cy=bulk-label] .toggler')
            .should('have.class', 'toggler_inactive')
            .click()
            .get('@url')
            .should('be.disabled')
            .get('[data-cy=add-keyword-web-keyword-textarea]')
            .type(`test.com:keyword ${dateNow}:tag ${dateNow}\ntest.com:keyword1 ${dateNow}:tag1 ${dateNow}`)
            .get('[data-cy=add-keyword-web-save]')
            .click()
            .get('[data-cy=simple-confirm-cancel]')
            .click()
            .get('.toast')
            .should('have.class', 'toast-success')
            .url()
            .should('include', '/groups/');
    });

    it('Map tab. Test for fields validations', () => {
        cy
            .get('.nav-tabs > li')
            .eq(1)
            .should('have.not.class', 'active')
            .click()
            .should('have.class', 'active');

        cy.urlNegativeTests('map');

        cy
            .get('[data-cy=add-keyword-map-keyword-textarea]')
            .should('have.class', 'text-input_invalid');
    });

    it('Map tab. Fill all fields and send request to add keyword', () => {
        cy
            .get('.nav-tabs > li')
            .eq(1)
            .should('have.not.class', 'active')
            .click()
            .should('have.class', 'active');

        cy
            .get('ul.dropdown-menu')
            .should('not.be.visible')
            .get('[data-cy=add-keyword-map-location]')
            .should('not.exist')
            .get('[data-cy=add-keyword-map-suggestions]')
            .should('not.exist');
        
        cy
            .get('[data-cy=add-keyword-map-url-input]')
            .as('url')
            .should('have.class', 'text-input')
            .type('test.com')
            .should('not.have.class', 'text-input_invalid');

        cy
            .get('[data-cy=add-keyword-map-region]')
            .as('region').click()
            .get('ul.dropdown-menu input.form-control')
            .eq(2).focus().type('google.ca')
            .get('ul.dropdown-menu')
            .eq(2).contains('li', 'google.ca')
            .click()
            .get('@region')
            .should('have.class', 'custom-dropdown_filled')
            .contains('google.ca');

        cy
            .get('[data-cy=add-keyword-map-location-label] .checkbox')
            .as('location-checkbox')
            .should('have.not.class', 'checkbox_active')
            .click()
            .and('have.class', 'checkbox_active')
            .get('[data-cy=add-keyword-map-location]')
            .as('location')
            .should('exist')
            .focus().type('Paris, France')
            .get('.pac-container')
            .eq(0).click()
            .get('@location')
            .should('have.class', 'text-input')
            .and('have.value', 'Paris, France');

        cy.keywordTextareaPositiveTests('map', dateNow);

        cy
            .get('[data-cy=add-keyword-cancel]')
            .click()
            .url()
            .should('include', '/groups/')
            .and('not.include', '/add')
            .get('[data-cy=add-keyword-draft-counter]')
            .contains('1')
            .get('@add-keyword')
            .click()
            .url()
            .should('include', '/add')
            .get('.nav-tabs > li')
            .eq(1)
            .click()
            .get('@url')
            .should('have.value', 'test.com')
            .get('@region')
            .should('have.class', 'custom-dropdown_filled')
            .contains('google.ca')
            .get('@location-checkbox')
            .should('have.class', 'checkbox_active')
            .get('@keywords')
            .should('have.not.class', 'text-input_invalid')
            .and('have.value', `keyword ${dateNow}`);
        
        cy
            .get('[data-cy=add-keyword-map-save]')
            .click()
            .wait(3000)
            .get('.toast')
            .should('have.class', 'toast-success')
            .url()
            .should('include', '/groups/');
    });

    it('Yt tab. Test for fields validations', () => {
        cy
            .get('.nav-tabs > li')
            .eq(2)
            .should('have.not.class', 'active')
            .click()
            .should('have.class', 'active');

        cy.urlNegativeTests('yt');
    });

    it('Yt tab. Fill all fields and send request to add keyword', () => {
        cy
            .get('.nav-tabs > li')
            .eq(2)
            .should('have.not.class', 'active')
            .click()
            .should('have.class', 'active');

        cy
            .get('ul.dropdown-menu')
            .should('not.be.visible')
            .get('[data-cy=add-keyword-yt-suggestions]')
            .should('not.exist');
        
        cy
            .get('[data-cy=add-keyword-yt-url-input]')
            .as('url')
            .should('have.class', 'text-input')
            .type('Hm-08DIMTT4')
            .should('not.have.class', 'text-input_invalid');

        cy
            .get('[data-cy=add-keyword-yt-region]')
            .as('region').click()
            .get('ul.dropdown-menu input.form-control')
            .eq(3).focus().type('youtube.ca')
            .get('ul.dropdown-menu')
            .eq(3).contains('li', 'youtube.ca')
            .click()
            .get('@region')
            .should('have.class', 'custom-dropdown_filled')
            .contains('youtube.ca');

        cy.keywordTextareaPositiveTests('yt', dateNow);

        cy
            .get('[data-cy=add-keyword-cancel]')
            .click()
            .url()
            .should('include', '/groups/')
            .and('not.include', '/add')
            .get('[data-cy=add-keyword-draft-counter]')
            .contains('1')
            .get('@add-keyword')
            .click()
            .url()
            .should('include', '/add')
            .get('.nav-tabs > li')
            .eq(2)
            .click()
            .get('@url')
            .should('have.value', 'Hm-08DIMTT4')
            .get('@region')
            .should('have.class', 'custom-dropdown_filled')
            .contains('youtube.ca')
            .get('@keywords')
            .should('have.not.class', 'text-input_invalid')
            .and('have.value', `keyword ${dateNow}`);
        
        cy
            .get('[data-cy=add-keyword-yt-save]')
            .click()
            .get('.toast')
            .should('have.class', 'toast-success')
            .url()
            .should('include', '/groups/');
    });
});