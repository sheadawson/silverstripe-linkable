import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';

class LinkField extends PureComponent {

    constructor(props) {
        super(props);
    }

    renderExisting() {
        const {
            id,
            name,
            value,
            label,
            url,
        } = this.props;

        return (
            <div>
                <a className="link" href={url} target="_blank" style={{ paddingRight: '10px' }}>{label}</a>
                <button href="#" className="linkfield-button btn btn-primary" onClick={this.handleOpenModal}>Edit</button>
                <button href="#" className="linkfield-remove-button btn btn-danger">Remove</button>
                <input id={id} name={name} value={value} type="text" className="link" style={{ display: 'none' }} />
                <div className='linkfield-dialog'></div>
            </div>
        );
    }

    renderNone() {
        const {
            id,
            name,
            value,
        } = this.props;

        return (
            <div>
                <button href="#" className="linkfield-button btn btn-primary font-icon-plus-circled" onClick={this.handleOpenModal}>Add Link</button>
                <input id={id} name={name} value={value} type="text" className="link" style={{ display: 'none' }} />
                <div className='linkfield-dialog'></div>
            </div>
        );
    }

    render() {
        const {
            id,
            value,
            title,
            holderId,
        } = this.props;

        let have_val = (value != undefined && value != '' && value != '0');

        return (
            <div className="field form-group">
                <label className="form__field-label" for={id}>{title}</label>
                <div id={holderId} className="form__field-holder">
                    {have_val ? this.renderExisting() : this.renderNone()}
                </div>
            </div>
        );
    }
}

LinkField.propTypes = {
    id: PropTypes.string,
    name: PropTypes.string,
    value: PropTypes.string,
    title: PropTypes.string,
    type: PropTypes.string,
    label: PropTypes.string,
    url: PropTypes.string,
    holderId: PropTypes.string,
};

LinkField.defaultProps = {
    id: '',
    name: '',
    value: '0',
    title: 'Link to page',
    type: 'text',
    label: '',
    url: '',
    holderId: '',
};

export default LinkField;