export default class AddressForm {
    constructor({
        address, postalCode, cityName, country,
    }) {
        this._address = address;
        this._postalCode = postalCode;
        this._cityName = cityName;
        this._country = country;
    }

    getAddressString() {
        return [
            this._address.value,
            this._cityName.value,
            this._postalCode.value,
            this._country.value,
        ].filter((item) => item).join(', ');
    }

    reset() {
        this._address.value = '';
        this._postalCode.value = '';
        this._cityName.value = '';
        this._country.value = '';
    }

    updateWithPlace({ address_components: addressComponents }) {
        this.reset();

        const placeData = {
            street_number: null,
            route: null,
            locality: null,
            postal_town: null,
            sublocality_level_1: null,
            postal_code: null,
            postal_code_prefix: null,
            country: null,
            administrative_area_level_1: null,
        };

        addressComponents.forEach((component) => {
            const type = component.types[0];
            if (type in placeData) {
                placeData[type] = component;
            }
        });

        this._address.value = ([
            (placeData.street_number && placeData.street_number.long_name || ''),
            (placeData.route && placeData.route.long_name || ''),
        ].join(' '));

        this._cityName.value = (
            (placeData.locality && placeData.locality.long_name)
            || (placeData.sublocality_level_1 && placeData.sublocality_level_1.long_name)
            || (placeData.postal_town && placeData.postal_town.long_name)
            || ''
        );

        this._postalCode.value = (
            (placeData.postal_code && placeData.postal_code.long_name)
            || (placeData.postal_code_prefix && placeData.postal_code_prefix.long_name)
            || ''
        );

        this._country.value = placeData.country.short_name || 'FR';
    }
}
