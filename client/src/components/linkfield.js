import * as React from 'react';
import PropTypes from 'prop-types';

class LinkField extends React.PureComponent {

    constructor(props) {
        super(props);
    }

    render() {
        const {
            id,
            name,
            title,
            label,
            value,
            url,
            holderId,
        } = this.props;

        let have_link = (value != undefined && value != '' && value != '0');

        let link = (<a className="link" href={url} style={{ paddingRight: '10px' }}>{label}</a>);
        let add = (<button href="#" className="linkfield-button btn btn-primary font-icon-plus-circled">Add Link</button>);
        let edit = (<button href="#" className="linkfield-button btn btn-primary">Edit</button>);
        let remove = (<button href="#" className="linkfield-remove-button btn btn-danger">Remove</button>);

        return (
            <div className="field form-group link" id={holderId}>
                <label className="form__field-label" id={`title-${id}`} htmlFor={id}>{title}</label>
                <div className="form__field-holder">
                    <div className='linkfield-dialog'></div>
                    <input type="hidden" className="link" id={id} name={name} value={value} />
                    {have_link && link}
                    {have_link && edit}
                    {have_link && remove}
                    {!have_link && add}
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