/**
 * Insolvenzo Form – Block Editor (Gutenberg)
 *
 * React-based editor implementation for the Insolvenzo insolvency form.
 * Responsibilities:
 * - Render editor preview of the multi-step form
 * - Manage block attributes (defined in block.json)
 * - Handle step logic, conditional visibility and user input
 *
 * This file is editor-only and does NOT control frontend output.
 */

import { useState } from '@wordpress/element';
import { Button, TextControl, TextareaControl, IconButton } from '@wordpress/components';
import { useBlockProps, BlockControls, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, PanelBody } from '@wordpress/components';
import { trash } from '@wordpress/icons';

const steps = [
	{ id: 1, roman: 'I', title: 'Eingangsabfrage' },
	{ id: 2, roman: 'II', title: 'Angaben zum Kontoinhaber und Pfändungsschutzkonto' },
	{ id: 3, roman: 'III', title: 'Ermittlung des pfändungsfreien Betrags' },
	{ id: 4, roman: 'IV', title: 'Laufende Geldleistungen' },
	{ id: 5, roman: 'V', title: 'Einmalige Zahlungen' },
];

function StepNav({ current, onSelect }) {
	return (
		<div className="insolvenzo-step-nav">
			{steps.map((s) => (
				<button
					key={s.id}
					className={`insolvenzo-step-nav__item ${current === s.id ? 'is-active' : ''}`}
					onClick={() => onSelect(s.id)}
					type="button"
				>
					<span className="insolvenzo-step-nav__roman">{s.roman}</span>
					<span className="insolvenzo-step-nav__title">{s.title}</span>
				</button>
			))}
		</div>
	);
}

function InfoBox({ title, content, videoUrl, onTitleChange, onContentChange, onVideoSelect, onVideoRemove, stepNum }) {
	return (
		<div className="insolvenzo-editor-info-boxes">
			{/* Text Box */}
			<div className="insolvenzo-editor-info-box">
				<div className="insolvenzo-editor-info-box-header">
					<strong>📝 Erklärung</strong>
				</div>
				<div className="insolvenzo-editor-info-box-content">
					<TextControl
						label="Titel"
						value={title}
						onChange={onTitleChange}
						placeholder="z.B. Wichtige Information"
					/>
					<TextareaControl
						label="Text"
						value={content}
						onChange={onContentChange}
						placeholder="Geben Sie hier Erklärungen ein..."
						rows={4}
					/>
				</div>
			</div>

			{/* Video Box */}
			<div className="insolvenzo-editor-info-box">
				<div className="insolvenzo-editor-info-box-header">
					<strong>🎥 Erklärungsvideo</strong>
				</div>
				<div className="insolvenzo-editor-info-box-content">
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) => onVideoSelect(media)}
							allowedTypes={['video']}
							value={videoUrl}
							render={({ open }) => (
								<div>
									{videoUrl ? (
										<div className="insolvenzo-video-preview">
											<video controls style={{ width: '100%', maxHeight: '200px' }}>
												<source src={videoUrl} type="video/mp4" />
												Ihr Browser unterstützt das Video-Tag nicht.
											</video>
											<Button
												isDestructive
												isSmall
												onClick={onVideoRemove}
												style={{ marginTop: '8px' }}
											>
												Video entfernen
											</Button>
										</div>
									) : (
										<Button isSecondary onClick={open}>
											Video hochladen
										</Button>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
				</div>
			</div>
		</div>
	);
}

function StepContent({ step, attributes, setAttributes }) {
	const stepNum = step;
	const textTitle = attributes[`step${stepNum}TextTitle`] || '';
	const textContent = attributes[`step${stepNum}TextContent`] || '';
	const videoUrl = attributes[`step${stepNum}VideoUrl`] || '';

	const handleTitleChange = (value) => {
		setAttributes({ [`step${stepNum}TextTitle`]: value });
	};

	const handleContentChange = (value) => {
		setAttributes({ [`step${stepNum}TextContent`]: value });
	};

	const handleVideoSelect = (media) => {
		setAttributes({
			[`step${stepNum}VideoUrl`]: media.url,
			[`step${stepNum}VideoId`]: media.id
		});
	};

	const handleVideoRemove = () => {
		setAttributes({
			[`step${stepNum}VideoUrl`]: '',
			[`step${stepNum}VideoId`]: 0
		});
	};

	return (
		<div className="insolvenzo-editor-step-wrapper">
			<div className="insolvenzo-editor-step-left">
				<h4>{steps[step - 1].title}</h4>
				<p style={{ fontSize: '0.9rem', color: '#666', marginBottom: '10px' }}>
					(Formularfelder werden nur im Frontend ausgefüllt)
				</p>
				{step === 3 && (
					<div style={{ marginBottom: '20px', padding: '10px', backgroundColor: '#f0f0f0', borderRadius: '4px' }}>
						<label style={{ display: 'block', fontWeight: 'bold', marginBottom: '8px' }}>
							Grundfreibetrag (€)
						</label>
						<input
							type="number"
							value={attributes.grundfreibetrag || 1499.99}
							onChange={(e) => setAttributes({ grundfreibetrag: parseFloat(e.target.value) || 1499.99 })}
							step="0.01"
							style={{
								width: '100%',
								padding: '8px',
								border: '1px solid #ddd',
								borderRadius: '4px',
								fontSize: '14px'
							}}
						/>
						<p style={{ fontSize: '0.85rem', color: '#666', marginTop: '6px', marginBottom: '0' }}>
							Der Grundfreibetrag wird systemseitig gemäß der gültigen Pfändungstabelle gesetzt.
						</p>

						<label style={{ display: 'block', fontWeight: 'bold', marginTop: '14px', marginBottom: '8px' }}>
							Zugerechneter Betrag je unterhaltspflichtige Person (€)
						</label>
						<input
							type="number"
							value={attributes.unterhaltspersonBetrag || 326.04}
							onChange={(e) => setAttributes({ unterhaltspersonBetrag: parseFloat(e.target.value) || 326.04 })}
							step="0.01"
							style={{
								width: '100%',
								padding: '8px',
								border: '1px solid #ddd',
								borderRadius: '4px',
								fontSize: '14px'
							}}
						/>
						<p style={{ fontSize: '0.85rem', color: '#666', marginTop: '6px', marginBottom: '0' }}>
							Dieser Wert wird in der Berechnung des Erhöhungsbetrags im Frontend verwendet.
						</p>
					</div>
				)}
			</div>
			<div className="insolvenzo-editor-step-right">
				<InfoBox
					title={textTitle}
					content={textContent}
					videoUrl={videoUrl}
					onTitleChange={handleTitleChange}
					onContentChange={handleContentChange}
					onVideoSelect={handleVideoSelect}
					onVideoRemove={handleVideoRemove}
					stepNum={stepNum}
				/>
			</div>
		</div>
	);
}

export default function Edit({ attributes, setAttributes, clientId }) {
	const [current, setCurrent] = useState(1);
	const blockProps = useBlockProps();

	function goNext() {
		setCurrent((c) => Math.min(c + 1, steps.length));
	}

	function goPrev() {
		setCurrent((c) => Math.max(c - 1, 1));
	}

	function handleDelete() {
		if (window.confirm('Möchten Sie diesen Block wirklich löschen?')) {
			// WordPress Gutenberg API zum Entfernen des Blocks
			wp.data.dispatch('core/block-editor').removeBlock(clientId);
		}
	}

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={trash}
						label="Block löschen"
						onClick={handleDelete}
					/>
				</ToolbarGroup>
			</BlockControls>

			<div {...blockProps} className="insolvenzo-form-editor">
				<h3>Insolvenzo Formular</h3>
				<StepNav current={current} onSelect={setCurrent} />

				<div className="insolvenzo-step-content">
					<h4>{`${steps[current - 1].roman} — ${steps[current - 1].title}`}</h4>
					<StepContent step={current} attributes={attributes} setAttributes={setAttributes} />
				</div>

				<div className="insolvenzo-step-actions">
					<Button isSecondary onClick={goPrev} disabled={current === 1}>
						Zurück
					</Button>
					<Button isPrimary onClick={goNext} disabled={current === steps.length}>
						Weiter
					</Button>
					<Button 
						isDestructive 
						onClick={handleDelete}
						style={{ marginLeft: 'auto' }}
					>
						Block löschen
					</Button>
				</div>
			</div>
		</>
	);
}
